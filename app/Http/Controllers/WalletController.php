<?php

namespace App\Http\Controllers;

use App\Models\BankAccounts;
use App\Models\Department;
use App\Services\PaystackServices;
use DB;
use Illuminate\Http\Request;
use App\Models\User;
use Bavix\Wallet\Models\Wallet;
use Illuminate\Support\Facades\Validator;
use Towoju5\Wallet\Services\WalletService;
use Unicodeveloper\Paystack\Facades\Paystack;
// use Towoju5\LaravelWallet\Services\CurrencyExchangeService;
use Towoju5\Wallet\Services\CurrencyExchangeService;

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
                        return $accountInfo = $this->createPaystackVirtualAccount();

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
                        exit();
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
        $validate = Validator::make($request->all(), [
            'amount' => 'required|min:100|numeric',
            'bank_id' => 'required|string',
            'narration' => 'sometimes|string'
        ]);

        if ($validate->fails()) {
            return get_error_response("Validation Error", $validate->errors());
        }

        $user = $request->user();
        $_where = [
            "id" => $request->bank_id,
            'user_id' => $user->id,
        ];
        
        if (!BankAccounts::where($_where)->exists()) {
            return get_error_response("Invalid bank account ID provided");
        }
        
        $wallet = $user->getWallet($walletType);
        $amount = $request->amount;
        $withdrawal_fee = get_settings_value('withdrawal_fee', 0);
        $finalAmountDue = $amount + $withdrawal_fee;
        if ($wallet->balance < $finalAmountDue) {
            return get_error_response('Insufficient funds', ['error' => 'Insufficient funds']);
        }

        try {
            $transaction = [];
            // $transaction = $wallet->withdraw($amount, 'ngn', $request->toArray());
            $transaction[] = $wallet->withdrawal($amount,  ['description' => "Withdrawal to bank account - {$request->bank_id}", "narration" => $request->narration ?? null]);
            $transaction[] = $wallet->withdrawal($withdrawal_fee, ['description' => "Withdrawal Fee - {$request->bank_id}", "narration" => "Charges for withdrawal to bank account - {$request->bank_id}"]);

            // send request to paystack for withdrawal
            $paystack = new PaystackServices();
            $payoutObject = [
                "amount" => $amount,
                "recipient" => $amount,
                "narration" => $amount,
            ];
            $processWithdrawal = $paystack->initiateTransfer($payoutObject);
            return get_success_response($transaction, 'Withdrawal successful');
        } catch (\Exception $e) {
            return get_error_response($e->getMessage());
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
            'to_wallet' => 'required|string'
        ]);
    
        if ($validate->fails()) {
            return get_error_response("Validation Error.", $validate->errors()->toArray());
        }
    
        $user = $request->user();
        $fromWalletType = $request->from_wallet;
        $toWalletType = $request->to_wallet;
        $amount = $request->amount;
    
        try {
    
            // Fetch wallets
            $from = $user->getWallet($fromWalletType);
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
        $wallet = $user->getWallet($walletType);
        $transactions = $wallet->transactions()->select('*')->where('_account_type', $user->current_role)->latest()->paginate(20); //->makeHidden();

        return get_success_response($transactions, 'Transactions retrieved successfully');
    }

    public function getAllWallets()
    {
        $user = auth()->user();
        $wallets = $user->my_wallets();
        if ($wallets->isEmpty() || count($wallets) < 1) {
            // generate wallet for user
            $mainWallet = $user->createWallet([
                'name' => 'Naira Wallet',
                'slug' => 'ngn',
                'meta' => [
                    'symbol' => '₦',
                    'code' => 'NGN',
                ],
            ]);

            if (!$mainWallet) {
                return get_error_response('Failed to create main wallet');
            }

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

            if (
                $account = BankAccounts::updateOrCreate(
                    [
                        "account_number" => $validate["account_number"],
                        "bank_code" => $validate["bank_code"]
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
        $paystack_secret_key = get_settings_value('paystack_secret_key', 'sk_test_390011d63d233cad6838504b657721883bc096ec');
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
}
