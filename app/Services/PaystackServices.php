<?php

namespace App\Services;

use App\Models\TransactionRecords;
use App\Models\Transactions;
use App\Models\BankAccounts;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Log;

class PaystackServices
{
    protected $paystackSecretKey;
    protected $paystackBaseUrl = 'https://api.paystack.co';

    public function __construct()
    {
        $this->paystackSecretKey = getenv("PAYSTACK_SECRET_KEY");
    }

    private function ensureCustomerId(User $user)
    {
        if (!$user->paystack_customer_id OR null == $user->paystack_customer_id) {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->paystackSecretKey,
                'Content-Type' => 'application/json',
            ])->post($this->paystackBaseUrl . '/customer', [
                        'email' => $user->email,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'phone' => $user->phone,
                    ]);

            if ($response->successful()) {
                $customerData = $response->json()['data'];
                $updated = $user->update(['paystack_customer_id' => $customerData['id']]);
                if ($updated) {
                    return true;
                }
            }

            return false;
        }
    }

    public function generateVirtualAccount()
    {
        $user = User::findOrFail(auth()->id());
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->paystackSecretKey,
            'Content-Type' => 'application/json',
        ])->post($this->paystackBaseUrl . '/dedicated_account', [
                    "email" => "janedoe@test.com",
                    "first_name" => "Jane",
                    "middle_name" => "Karen",
                    "last_name" => "Doe",
                    "phone" => "+2348100000000",
                    "preferred_bank" => "test-bank",
                    "country" => "NG"
                ]);

        Log::info('Virtual account creation response:', ['response' => $response->json()]);

        if ($response->successful()) {
            $accountData = $response->json()['data'];

            BankAccounts::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'account_type' => 'deposit',
                ],
                [
                    'account_name' => $accountData['account_name'],
                    'bank_name' => $accountData['bank']['name'],
                    'account_number' => $accountData['account_number'],
                    'bank_code' => $accountData['bank']['code'],
                    'provider_response' => json_encode($accountData),
                    'provider_name' => 'paystack',
                ]
            );

            return $accountData;
        }

        return ['error' => $response->reason()];
    }

    public function initiatePayment($amount)
    {
        $amount = $amount * 100; // Convert to kobo

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->paystackSecretKey,
            'Content-Type' => 'application/json',
        ])->post($this->paystackBaseUrl . '/transaction/initialize', [
                    'email' => auth()->user()->email,
                    'amount' => $amount,
                ]);

        if ($response->successful()) {
            $transaction = $response->json();
            return $transaction['data']['authorization_url'];
        } else {
            throw new \Exception('Failed to initialize Paystack transaction');
        }
    }

    public function verifyPayment($reference)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->paystackSecretKey,
            'Content-Type' => 'application/json',
        ])->get($this->paystackBaseUrl . "/transaction/verify/{$reference}");

        if ($response->successful()) {
            $paymentData = $response->json()['data'];
            return response()->json(['message' => 'Payment verified', 'data' => $paymentData]);
        }

        return response()->json(['error' => 'Failed to verify payment'], 500);
    }

    public function handleWebhook(Request $request)
    {
        $paystackSignature = $request->header('x-paystack-signature');
        $computedSignature = hash_hmac('sha512', $request->getContent(), $this->paystackSecretKey);

        if ($paystackSignature !== $computedSignature) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $payload = $request->all();
        $event = $payload['event'];

        switch ($event) {
            case 'charge.success':
                $this->handleSuccessfulCharge($payload['data']);
                break;
            case 'transfer.success':
                $this->handleSuccessfulTransfer($payload['data']);
                break;
            case 'dedicated_account.assign.success':
                $this->handleVirtualAccountCreation($payload['data']);
                break;
        }

        return response()->json(['message' => 'Webhook processed']);
    }

    public function initiateTransfer($data)
    {
        $url = $this->paystackBaseUrl . '/transfer';
        $request = request();
        $payload = [
            'source' => "balance",
            'amount' => $data['amount'],
            'recipient' => $data['recipient'],
            'reason' => $data['narration'] ?? $request->narration,
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->paystackSecretKey,
        ])->post($url, $payload);

        if ($response->successful()) {
            return ['success' => true, 'data' => $response->json()['data']];
        }

        return ['success' => false, 'message' => $response->json()['message'] ?? 'Transfer initiation failed'];
    }

    public function handleSuccessfulCharge($data)
    {
        $user = User::where('email', $data['customer']['email'])->first();
        Log::info('Paystack charge successful', ['data' => $data, 'user' => $user]);
        $walletId = Wallet::where([
                        'user_id' => $user->id,
                        'currency' => get_default_currency($user->id)
                    ])->first();
        if ($user) {
            TransactionRecords::create([
                'user_id' => $user->id,
                'wallet_id' => $walletId,
                'transaction_reference' => $data['reference'],
                'transaction_type' => 'credit',
                'transaction_slug' => 'paystack_charge',
                'transaction_status' => 'successful',
                'transaction_amount' => $data['amount'] / 100, // Convert from kobo to naira
            ]);
            $user->deposit($data['amount'], $data['currency'], $data['metadata']['_account_type'], ['source' => 'Card payment via Paystack'], 'Card payment via Paystack');
            $user->deposit($data['amount'] * get_settings_value('recharge_points'), 'bonus', $data['metadata']['_account_type'], ['source' => 'Bonus for wallet topup'], 'Bonus for wallet topup');
            $user->wallet->deposit($data['amount']);
        }
    }

    public function handleSuccessfulTransfer($data)
    {
        // Handle successful transfers (payouts)
        $user = User::where('email', $data['customer']['email'])->first();
        if ($user) {
            TransactionRecords::create([
                'user_id' => $user->id,
                'wallet_id' => $user->wallet->id,
                'transaction_reference' => $data['reference'],
                'transaction_type' => 'credit',
                'transaction_slug' => 'paystack_charge',
                'transaction_status' => 'successful',
                'transaction_amount' => $data['amount'] / 100, // Convert from kobo to naira
            ]);
            $user->deposit($data['amount'], $data['currency'], $data['metadata']['_account_type'], ['source' => 'Card payment via Paystack'], 'Card payment via Paystack');
            $user->deposit(0.05, 'bonus', $data['metadata']['_account_type'], ['source' => 'Bonus for wallet topup'], 'Bonus for wallet topup');
            $user->wallet->deposit($data['amount']);
        }
    }

    public function handleVirtualAccountCreation($data)
    {
        $user = User::where('paystack_customer_id', $data['customer']['id'])->first();
        if ($user) {
            BankAccounts::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'account_type' => 'deposit'
                ],
                [
                    'account_name' => $data['account_name'],
                    'bank_name' => $data['bank']['name'],
                    'account_number' => $data['account_number'],
                    'bank_code' => $data['bank']['code'],
                    'provider_response' => json_encode($data),
                    'provider_name' => 'paystack',
                ]
            );
        }
    }
}
