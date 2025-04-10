<?php

namespace App\Services;

use App\Models\TransactionRecords;
use App\Models\Transactions;
use App\Models\BankAccounts;
use Towoju5\Wallet\Models\Wallet;
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
        $this->paystackSecretKey = get_settings_value('paystack_secret_key');
    }

    private function ensureCustomerId(User $user)
    {
        if (!$user->paystack_customer_id or null == $user->paystack_customer_id) {
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
        try {
            $url = $this->paystackBaseUrl . '/transfer';
            $payload = [
                'source' => "balance",
                'amount' => ceil($data['amount']),
                'reference' => generate_uuid(),
                'recipient' => $data['recipient'],
                'reason' => $data['narration'] ?? 'Fund Transfer',
            ];
    
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '. $this->paystackSecretKey,
            ])->post($url, $payload);
    
            Log::info("Paystack Payout Request", ['payload' => $payload, 'response' => $response->json()]);
    
            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()['data']];
            }
    
            return ['success' => false, 'message' => $response->json()['message'] ?? 'Transfer initiation failed'];
        } catch (\Exception $e) {
            Log::error("Paystack Transfer Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while processing the transfer'];
        }
    }
    

    public function handleSuccessfulCharge($data)
    {
        Log::info('Webhook received', ['data' => $data]);

        // Find user by email
        $user = User::where('email', $data['customer']['email'])->first();
        if (!$user) {
            Log::error('User not found for email', ['email' => $data['customer']['email']]);
            return;
        }

        // Validate default currency and role
        $currency = get_default_currency($user->id);
        $role = $user->current_role;
        if (!$currency || !$role) {
            Log::error('Invalid currency or role', [
                'user_id' => $user->id,
                'currency' => $currency,
                'role' => $role,
            ]);
            return;
        }

        // Locate wallet
        $wallet = Wallet::where([
            'user_id' => $user->id,
            'currency' => $currency,
            'role' => $role,
        ])->first();
        if (!$wallet) {
            Log::error('Wallet not found', [
                'user_id' => $user->id,
                'currency' => $currency,
                'role' => $role,
            ]);
            return;
        }

        // Retrieve primary and bonus wallets
        $wallet1 = $user->getWallet($data['currency']);
        $wallet2 = $user->getWallet('bonus');

        if (!$wallet1) {
            Log::error('Primary wallet not found', ['currency' => $data['currency']]);
            return;
        }
        if (!$wallet2) {
            Log::error('Bonus wallet not found');
            return;
        }

        // Deposit into wallets
        try {
            $wallet1->deposit($data['amount'], [
                'source' => 'Card payment via Paystack',
                'description' => 'Wallet topup',
            ]);

            $bonusAmount = $data['amount'] * get_settings_value('recharge_points');
            $wallet2->deposit($bonusAmount, [
                'source' => 'Bonus for wallet topup',
                'description' => 'Bonus for wallet topup',
            ]);

            Log::info('Deposit successful', [
                'wallet1_balance' => $wallet1->balance,
                'wallet2_balance' => $wallet2->balance,
            ]);

            // Create transaction record
            $txn = new TransactionRecords;
            $txn->user_id = $user->id;
            $txn->wallet_id = $wallet1->id;
            $txn->transaction_reference = $data['reference'];
            $txn->transaction_type = 'credit';
            $txn->transaction_slug = 'paystack_charge';
            $txn->transaction_status = 'successful';
            $txn->transaction_amount = $data['amount'] / 100; // Convert from kobo to naira

            if (!$txn->save()) {
                Log::error("Unable to add Deposit transaction record for WalletID: $wallet1->id");
            }
            // Create transaction record
            $txn->user_id = $user->id;
            $txn->wallet_id = $wallet2->id;
            $txn->transaction_reference = $data['reference'];
            $txn->transaction_type = 'credit';
            $txn->transaction_slug = 'topup_bonus';
            $txn->transaction_status = 'successful';
            $txn->transaction_amount = $data['amount'] / 100; // Convert from kobo to naira

            if (!$txn->save()) {
                Log::error("Unable to add Deposit transaction record for WalletID: $wallet2->id (Bonus wallet)");
            }
        } catch (\Throwable $th) {
            Log::error('Deposit failed', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);
        }
    }


    public function handleSuccessfulTransfer($data)
    {
        if(!isset($data['customer']['email'])) {
            return false;
        }
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
            $user->deposit($data['amount'], $data['currency'], $user->current_role, ['source' => 'Card payment via Paystack'], 'Card payment via Paystack');
            $user->deposit(0.05, 'bonus', $user->current_role, ['source' => 'Bonus for wallet topup'], 'Bonus for wallet topup');
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
