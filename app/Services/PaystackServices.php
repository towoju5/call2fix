<?php

namespace App\Services;

use App\Models\TransactionRecords;
use App\Models\Transactions;
use App\Models\BankAccounts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;

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
        if (!$user->paystack_customer_id) {
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
                return $customerData = $response->json()['data'];
                $user->update(['paystack_customer_id' => $customerData['customer_code']]);
            } else {
                throw new \Exception('Failed to create Paystack customer');
            }
        }
    }

    public function generateVirtualAccount()
    {
        $user = User::findOrFail(auth()->id());
        if(!$this->ensureCustomerId($user)) {
            throw new \Exception('Failed to create Paystack customer');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->paystackSecretKey,
            'Content-Type' => 'application/json',
        ])->post($this->paystackBaseUrl . '/dedicated_account', [
            'customer' => $user->paystack_customer_id,
            'preferred_bank' => 'test-bank',
        ]);

        if ($response->successful()) {
            $accountData = $response->json()['data'];
            
            BankAccounts::updateOrCreate(
                ['user_id' => $user->id],
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

        $payload = [
            'source' => $data['source'],
            'amount' => $data['amount'],
            'recipient' => $data['recipient'],
            'reason' => $data['reason'],
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
            $user->deposit($data['currency'], $data['metadata']['_account_type'], $data['amount'], ['source' => 'Card payment via Paystack'], 'Card payment via Paystack');
            $user->wallet->deposit($data['amount']);
        }
    }

    public function handleSuccessfulTransfer($data)
    {
        // Handle successful transfers (payouts)
    }

    public function handleVirtualAccountCreation($data)
    {
        $user = User::where('paystack_customer_id', $data['customer']['customer_code'])->first();
        if ($user) {
            BankAccounts::updateOrCreate(
                ['user_id' => $user->id],
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
