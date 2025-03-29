<?php

namespace App\Http\Controllers;

use App\Models\BankAccounts;
use App\Models\Department;
use App\Services\PaystackServices;
use DB;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Withdrawal;
// use Bavix\Wallet\Models\Wallet;
use Towoju5\Wallet\Models\Wallet;
use Illuminate\Support\Facades\Validator;
use Towoju5\Wallet\Services\WalletService;
use Unicodeveloper\Paystack\Facades\Paystack;
// use Towoju5\LaravelWallet\Services\CurrencyExchangeService;
use Towoju5\Wallet\Services\CurrencyExchangeService;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class WalletController extends Controller
{
    public function deposit(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'amount' => 'sometimes|min:100|numeric',
                'payment_mode' => 'required|in:credit_card,bank_transfer',
            ]);

            if ($validate->fails()) {
                return get_error_response($validate->errors());
            }

            $user = auth()->user();

            switch ($request->payment_mode) {
                case 'bank_transfer':
                    $bankAccount = BankAccounts::where([
                        'user_id' => auth()->id(), 
                        'account_type' => 'withdrawal',
                        '_account_type' => active_role()
                    ])->first();
                    if (!$bankAccount) {
                        // generate deposit account for customer
                        $accountInfo = $this->createPaystackVirtualAccount();

                        if (isset($accountInfo['error'])) {
                            return get_error_response($accountInfo['error'], ['error' => 'Failed to create account']);
                        }
                        // var_dump($accountInfo); exit;

                        $user->bankAccount()->create([
                            'account_number' => $accountInfo['account_number'],
                            'account_name' => $accountInfo['account_name'],
                            'bank_name' => $accountInfo['bank_name'],
                            'bank_code' => $accountInfo['bank_code'],
                            'account_type' => 'withdrawal',
                            'provider_response' => $accountInfo['provider_response'] ?? null,
                        ]);

                        return get_success_response($accountInfo, 'Account info created and retrieved successfully');
                    }

                    if ($bankAccount) {
                        $accountInfo = [
                            'account_number' => $bankAccount->account_number,
                            'account_name' => $bankAccount->account_name,
                            'bank_name' => $bankAccount->bank_name,
                            'bank_code' => $bankAccount->bank_code,
                        ];
                        return get_success_response($accountInfo, 'Account info retrieved successfully');
                    }

                    return get_error_response('Unable to retrieve bank account', ['error' => 'Unable to retrieve bank account']);
                case 'credit_card':                   
                    $response = $this->initializePaystackPayment($request->amount);
                    
                    if ($response && isset($response['data']['authorization_url'])) {
                        return get_success_response($response['data']);
                    } 
                    
                    return get_error_response(['error' => "Payment initialization failed: " . ($response['message'] ?? "Unknown error")]);
            }
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ['error' => $th->getMessage()]);
        }
    }

    public function processDeposit(Request $request, $walletType = 'ngn')
    {
        $user = $request->user();
        $wallet = $user->getWallet($walletType);
        $amount = $request->amount * 100;

        try {
            $transaction = credit_user($user->id, $amount);
            return get_success_response($transaction, 'Deposit successful');
        } catch (\Exception $e) {
            return get_error_response($e->getMessage());
        }
    }

    public function withdraw(Request $request, $walletType)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'bank_id' => 'required|string|exists:bank_accounts,id',
            'narration' => 'nullable|string|max:255'
        ]);
    
        if ($validator->fails()) {
            return get_error_response("Validation Error", $validator->errors());
        }
    
        try {
            $user = $request->user();
            $bank_id = $request->bank_id;
            $amount = $request->amount;
            $withdrawal_fee = get_settings_value('withdrawal_fee', 0);
            $finalAmountDue = ($amount + $withdrawal_fee) * 100; // Convert to cents
    
            $account = BankAccounts::where([
                'id' => $bank_id,
                'user_id' => $user->id,
            ])->first();
    
            if (!$account || empty($account->account_reference)) {
                return get_error_response("Invalid bank account", ['error' => "Invalid or incomplete bank details"]);
            }
    
            $wallet = $user->getWallet($walletType);
            if ($wallet->balance < $finalAmountDue) {
                return get_error_response("Insufficient funds", ['error' => "Not enough balance for withdrawal"]);
            }
    
            // Generate unique transaction reference
            $transactionReference = generate_uuid();
    
            DB::beginTransaction();
            try {
                // **Step 1: Log Withdrawal as Pending**
                $withdrawal = Withdrawal::create([
                    'user_id' => $user->id,
                    'bank_id' => $bank_id,
                    'amount' => $amount,
                    'fee' => $withdrawal_fee,
                    'status' => 'pending',
                    'transaction_reference' => $transactionReference,
                    'meta' => []
                ]);
    
                // **Step 2: Deduct the Amount & Fee Separately**
                $withdrawalTransaction = $wallet->withdraw($amount * 100, [
                    'description' => "Withdrawal to bank - {$bank_id}",
                    'narration' => $request->narration ?? 'Personal Use'
                ]);
    
                $feeTransaction = $wallet->withdraw($withdrawal_fee * 100, [
                    'description' => "Withdrawal Fee",
                    'narration' => "Fee for bank transfer"
                ]);
    
                // **Step 3: Call Paystack API**
                $paystack = new PaystackServices();
                $payoutObject = [
                    'amount' => $amount * 100, // In cents
                    'recipient' => $account->account_reference,
                    'narration' => $request->narration ?? 'Personal Use',
                    'reference' => $transactionReference
                ];
    
                $paystackResponse = $paystack->initiateTransfer($payoutObject);
    
                if (!$paystackResponse['success']) {
                    // **Step 4: Refund on Failure**
                    $wallet->deposit($withdrawalTransaction->amount, [
                        'description' => "Refund for failed withdrawal",
                        'narration' => "Refund after failed Paystack transfer"
                    ]);
    
                    $wallet->deposit($feeTransaction->amount, [
                        'description' => "Refund for withdrawal fee",
                        'narration' => "Reversal of withdrawal fee"
                    ]);
    
                    // Update withdrawal status to "failed"
                    $withdrawal->update([
                        'status' => 'failed',
                        'meta' => array_merge(['meta' => $withdrawal->meta], ['gateway_response' => $paystackResponse])
                    ]);
    
                    DB::rollBack();
                    return get_error_response(
                        "Withdrawal failed: " . $paystackResponse['message'],
                        ['error' => $paystackResponse['message']]
                    );
                }
    
                // **Step 5: Update Withdrawal Status to Completed**
                $withdrawal->update([
                    'status' => 'completed',
                    'transaction_reference' => $paystackResponse['data']['reference'],
                    'meta' => array_merge(['meta' => $withdrawal->meta], [
                        'gateway_response' => $paystackResponse,
                        'wallet_record' => [$withdrawalTransaction, $feeTransaction],
                        'payout_payload' => $payoutObject
                    ])
                ]);
    
                DB::commit();
    
                return get_success_response([
                    "amount" => $paystackResponse['data']['amount'],
                    "currency" => $paystackResponse['data']['currency'],
                    "payout_status" => $paystackResponse['data']['status'],
                ], 'Withdrawal initiated successfully');
    
            } catch (\Exception $e) {
                DB::rollBack();
                logger('Withdrawal failed: ' . $e->getMessage());
                return get_error_response("An error occurred during withdrawal processing", ['error' => $e->getMessage()]);
            }
        } catch (\Exception $e) {
            logger('Withdrawal error: ' . $e->getMessage());
            return get_error_response("Withdrawal failed", ['error' => "An unexpected error occurred"]);
        }
    }    

    public function balance($walletType)
    {
        $user = auth()->user();
        $wallet = $user->getWallet($walletType);

        return get_success_response($wallet, 'Balance retrieved successfully');
    }

    public function transfer(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'from_wallet' => 'required|string',
            'to_wallet' => 'required|string',
            'user_id' => 'sometimes'
        ]);
    
        if ($validate->fails()) {
            return get_error_response("Validation Error.", $validate->errors()->toArray());
        }
    
        $user = $request->user();
        $fromWalletType = $request->from_wallet;
        $toWalletType = $request->to_wallet;
        $amount = $request->amount;
        
        if($request->has('user_id') && !empty($request->user_id)) {
            $user = User::whereId($request->user_id)->first();
            $toWalletType = "Department ID: {$request->user_id}";
        }
    
        try {
    
            // Fetch wallets
            $from = auth()->user()->getWallet($fromWalletType);
            $to = $user->getWallet($toWalletType);
    
            if (!$from || !$to) {
                return get_error_response("Wallet not found.", [
                    'from_wallet' => $fromWalletType,
                    'to_wallet' => $toWalletType
                ]);
            }
    
            DB::beginTransaction();
    
            // Perform transfer
            $from->withdrawal($amount, [
                "description" => 'Wallet Transfer',
                "details" => "Transfer from {$fromWalletType} to {$toWalletType}",
                "amount" => $amount
            ]);
    
            // If transferring from bonus wallet, apply the conversion ratio
            if (strtolower($fromWalletType) === "bonus") {
                $convertRatio = get_settings_value('claim_point_ratio');
    
                if (!is_numeric($convertRatio) || $convertRatio <= 0) {
                    return get_error_response("Invalid bonus point conversion ratio.", [
                        'claim_point_ratio' => 'The conversion ratio must be a positive number.'
                    ]);
                }
    
                $amount = floatval($amount * $convertRatio); 
            }
            
            $to->deposit($amount, [
                "description" => 'Wallet Transfer',
                "details" => "Transfer from {$fromWalletType} to {$toWalletType}",
                "amount" => $amount
            ]);
    
            DB::commit();
    
            return get_success_response(['wallet' => [$from, $to]], 'Transfer successful');
        } catch (\Exception $e) {
            DB::rollBack();
            return get_error_response($e->getMessage(), ['error' => $e->getMessage()]);
        }
    }

    public function transactions($walletType)
    {
        $user = auth()->user();

        // Check if user has a parent account
        if ($user->parent_account_id) {
            $user = User::where('id', $user->parent_account_id);
        }

        $wallet = $user->getWallet($walletType ?? 'ngn');
        $transactions = $wallet->transactions()->select('*')->where('_account_type', $user->current_role)->latest()->paginate(20); //->makeHidden();

        return get_success_response($transactions, 'Transactions retrieved successfully');
    }

    public function getAllWallets()
    {
        $user = auth()->user();
        if ($user && !empty($user->parent_account_id) && $user->main_account_role === 'private_accounts') {
            $user = User::whereId($user->parent_account_id)->first();
        }

        $wallets = Wallet::where('user_id', $user->id)->where('role', active_role())->get();
        if ($wallets->isEmpty() || count($wallets) < 1) {
            // generate wallet for user
            $mainWallet = $user->createWallet('ngn');
            // $mainWallet = $user->createWallet([
            //     'name' => 'Naira Wallet',
            //     'slug' => 'ngn',
            //     'meta' => [
            //         'symbol' => '₦',
            //         'code' => 'NGN',
            //     ],
            // ]);

            if (!$mainWallet) {
                return get_error_response('Failed to create main wallet');
            }

            $bonusWallet = $user->createWallet('bonus');
            $bonusWallet = $user->createWallet([
                'name' => 'Bonus Wallet',
                'slug' => 'bonus',
                'meta' => [
                    'symbol' => '₱',
                    'code' => 'bonus',
                ]
            ]);

            if (!$bonusWallet) {
                return get_error_response('Failed to create bonus wallet');
            }
            $wallets = $user->my_wallets();
        }
        return get_success_response($wallets, 'All wallets retrieved successfully');
    }

    public function addNewWallet(Request $request)
    {
        try {
            $user = auth()->user();

            if ($request->has('department_id')) {
                $user = Department::whereId($request->department_id)->where('owner_id', auth()->id())->first();
            }

            if (!$user) {
                return get_error_response('Department not found', ['error' => 'Department not found']);
            }

            if ($user->getWallet($request->wallet_slug)) {
                return get_error_response('Wallet already exists');
            }

            $walletName = $request->input('wallet_name');
            $walletSlug = $request->input('wallet_slug');

            $wallet = $user->createWallet([
                'name' => $walletName,
                'slug' => $walletSlug,
                'meta' => [
                    'symbol' => 'w',
                    'code' => sha1(time()),
                ],
            ]);

            return get_success_response($wallet, 'New wallet added successfully');
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ['error' => $th->getMessage()]);
        }
    }

    public function addBankAccount(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                "account_name" => "required|string",
                "bank_name" => "required|string",
                "account_number" => "required|string",
                "bank_code" => "required|string",
            ]);

            $validate = $validate->validated();

            $validate['user_id'] = auth()->id();
            $validate['account_type'] = 'withdrawal';

            // create the account as a paystack recipient
            $paystack_secret_key = get_settings_value('paystack_secret_key');
            if(!isset($paystack_secret_key) || empty($paystack_secret_key)) {
                return get_error_response("Unable to process withdrawal, please contact support", ['error' => "Unable to process withdrawal, please contact support"]);
            }
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$paystack_secret_key,
                'Content-Type' => 'application/json',
            ])->post('https://api.paystack.co/transferrecipient', [
                'type' => 'nuban',
                'name' => $validate['account_name'],
                'account_number' => $validate['account_number'],
                'bank_code' => $validate['bank_code'],
                'currency' => 'NGN'
            ]);

            // To get the response body
            $responseBody = $response->json();
            if($responseBody["status"] != true){
                return get_error_response($responseBody["message"], ['error' => $responseBody["message"]]);
            }

            if (!Schema::hasColumn('bank_accounts', 'account_reference')) {
                Schema::table('bank_accounts', function (Blueprint $table) {
                    $table->string('account_reference')->nullable();
                });
            }

            if (
                $account = BankAccounts::updateOrCreate(
                    [
                        "account_number" => $validate["account_number"],
                        "bank_code" => $validate["bank_code"],
                        "account_reference" => $responseBody['data']['recipient_code']
                    ],
                    $validate
                )
            ) {
                return get_success_response($account, "Bank account processed successfully", 200);
            }
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ['error' => $th->getMessage()]);
        }
    }

    public function getBankAccount()
    {
        try {
            $accounts = BankAccounts::where(['user_id' => auth()->id(), 'account_type' => 'withdrawal'])->get();
            return get_success_response($accounts, "Bank accounts retrieved successfully", 200);
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ['error' => $th->getMessage()]);
        }
    }

    public function getSingleBankAccount($id)
    {
        try {
            $account = BankAccounts::where(['id' => $id, 'user_id' => auth()->id(), 'account_type' => 'withdrawal'])->firstOrFail();
            return get_success_response($account, "Bank account retrieved successfully", 200);
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ['error' => $th->getMessage()]);
        }
    }

    public function deleteBankAccount($id)
    {
        try {
            $account = BankAccounts::where(['id' => $id, 'user_id' => auth()->id(), 'account_type' => 'withdrawal'])->firstOrFail();
            if ($account->delete()) {
                return get_success_response(null, "Bank account deleted successfully", 200);
            }
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ['error' => $th->getMessage()]);
        }
    }

    private function initializePaystackPayment($amount) {
        $paystack_secret_key = get_settings_value('paystack_secret_key', 'sk_test_390011d63d233cad6838504b657721883bc096ec');
    
        $url = 'https://api.paystack.co/transaction/initialize';
        $user_country = auth()->user()->country->currency_code ?? 'NGN';

        $fields = [
            'email' => auth()->user()->email,
            'amount' => $amount * 100,
            'currency' => $user_country,
            'callback_url' => route('paystack.callback'),
            'metadata' => [
                "_account_type" => active_role(),
                "user_id" => auth()->id()
            ]
        ];
    
        $fields_string = json_encode($fields);
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $paystack_secret_key",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    
        $response = curl_exec($ch);
        curl_close($ch);
    
        return json_decode($response, true);
    }

    private function createPaystackVirtualAccount() {
        $user = auth()->user();
        $customer = $this->createCustomer();
        if(!$customer) {
            return ['error' => $customer];
        }

        $fields = [
            "customer" => $customer['data']['id'],
            "preferred_bank" => "test-bank"
        ];
        
        $endpoint = "dedicated_account";
        return $this->processPaystack($endpoint, $fields);
    }

    public function createCustomer()
    {
        $endpoint = "customer";
        $user = auth()->user();
        $fields = [
            "email" => $user->email,
            "first_name" => $user->first_name,
            "last_name" => $user->last_name,
            "phone" => $user->phone
        ];
        return $this->processPaystack($endpoint, $fields);
    }

    private function processPaystack(string $endpoint, array $payload)
    {
        $paystack_secret_key = get_settings_value('paystack_secret_key');
        $url = "https://api.paystack.co/{$endpoint}";
            
        $fields_string = json_encode($payload);
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $paystack_secret_key",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    
        $response = curl_exec($ch);
        curl_close($ch);
    
        return json_decode($response, true);
    }
    
    public function withdrawalData($walletType)
    {
        try {
            $user = auth()->user();
            $wallet = $user->getWallet($walletType);
    
            // Get date ranges
            $currentMonthStart = now()->startOfMonth();
            $currentMonthEnd = now()->endOfMonth();
            $previousMonthStart = now()->subMonth()->startOfMonth();
            $previousMonthEnd = now()->subMonth()->endOfMonth();
    
            // Fetch all relevant transactions at once
            $transactions = $wallet->transactions()
                ->whereIn('type', ['withdrawal', 'deposit'])
                ->where('_account_type', $user->current_role)
                ->whereBetween('created_at', [$previousMonthStart, $currentMonthEnd])
                ->get();
    
            // Group transactions
            $total_payout = $transactions->where('type', 'withdrawal');
            $total_payout_current_month = $total_payout->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd]);
            $total_payout_previous_month = $total_payout->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd]);
    
            $total_deposit = $transactions->where('type', 'deposit');
            $total_earned_current_month = $total_deposit->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd]);
    
            // Calculate total amounts
            $sum_total_payout = $total_payout->sum('amount');
            $sum_total_payout_current_month = $total_payout_current_month->sum('amount');
            $sum_total_payout_previous_month = $total_payout_previous_month->sum('amount');
    
            // Calculate percentage difference safely
            $percentage_difference = $sum_total_payout_previous_month > 0
                ? (($sum_total_payout_current_month - $sum_total_payout_previous_month) / $sum_total_payout_previous_month) * 100
                : ($sum_total_payout_current_month > 0 ? 100 : 0);
    
            // Build response
            $response = [
                "total_payout" => $sum_total_payout,
                "total_payout_current_month" => $sum_total_payout_current_month,
                "total_deposit" => $total_deposit->sum('amount'),
                "total_earned_current_month" => $total_earned_current_month->sum('amount'),
                "total_payout_previous_month" => $sum_total_payout_previous_month,
    
                "count_total_payout" => $total_payout->count(),
                "count_total_payout_current_month" => $total_payout_current_month->count(),
                "count_total_deposit" => $total_deposit->count(),
                "count_total_earned_current_month" => $total_earned_current_month->count(),
                "count_total_payout_previous_month" => $total_payout_previous_month->count(),
    
                "percentage_difference" => round($percentage_difference, 2)
            ];
    
            return get_success_response($response, 'Transactions retrieved successfully');
        } catch (\Throwable $th) {
            return get_error_response('Something went wrong', ['error' => $th->getMessage()], 500);
        }
    }
}
