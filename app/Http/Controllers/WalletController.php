<?php

namespace App\Http\Controllers;

use App\Models\BankAccounts;
use Illuminate\Http\Request;
use App\Models\User;
use Bavix\Wallet\Models\Wallet;
use Illuminate\Support\Facades\Validator;
use Unicodeveloper\Paystack\Facades\Paystack;

class WalletController extends Controller
{
    public function deposit(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'amount' => 'required|min:100|numeric',
                'payment_mode' => 'required|in:credit_card,bank_transfer',
            ]);

            if ($validate->fails()) {
                return get_error_response($validate->errors());
            }

            $user = auth()->user();

            switch ($request->payment_mode) {
                case 'bank_transfer':
                    $bankAccount = $user->bankAccount()->first();
                    if (!$bankAccount) {
                        // generate deposit account for customer
                        $accountInfo = BankAccounts::generateAccount();

                        if (isset($accountInfo['error'])) {
                            return get_error_response($accountInfo['error'], 'Failed to create account');
                        }
                        // var_dump($accountInfo); exit;

                        $user->bankAccount()->create([
                            'account_number' => $accountInfo['account_number'],
                            'account_name' => $accountInfo['account_name'],
                            'bank_name' => $accountInfo['bank_name'],
                            'bank_code' => $accountInfo['bank_code'],
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
                    $paystack = new Paystack();
                    $data = [
                        'amount' => $request->amount,
                    ];
                    $checkoutUrl = $paystack->makePaymentRequest($data);
                    return get_success_response($checkoutUrl);
            }
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage());
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
            'bank_id' => 'required|exists:bank_accounts,id'
        ]);

        if ($validate->fails()) {
            return get_error_response("Validation Error", $validate->errors());
        }

        $user = $request->user();
        $wallet = $user->getWallet($walletType);
        $amount = $request->amount;
        $withdrawal_fee = get_settings_value('withdrawal_fee', 0);

        if ($wallet->balance < $amount + $withdrawal_fee()) {
            return get_error_response('Insufficient funds', ['error' => 'Insufficient funds']);
        }

        try {
            $transaction = $wallet->withdraw($amount, 'ngn', $request->toArray());
            $transaction = $wallet->withdraw($amount, 'ngn', ['message', "Withdrawal fee for {$transaction->id}"]);

            // send request to paystack for withdrawal
            //
            return get_success_response($transaction, 'Withdrawal successful');
        } catch (\Exception $e) {
            return get_error_response($e->getMessage());
        }
    }

    public function balance($walletType)
    {
        $user = auth()->user();
        $wallet = $user->getWallet($walletType);

        return get_success_response($wallet->only([
            "name",
            "slug",
            "meta",
            "balance",
            "decimal_places",
        ]), 'Balance retrieved successfully');
    }

    public function transfer(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'amount' => 'required|min:100|numeric',
            'from_wallet' => 'required',
            'to_wallet' => 'required'
        ]);

        if ($validate->fails()) {
            return get_error_response($validate->errors());
        }

        $user = $request->user();
        $fromWalletType = $request->from_wallet;
        $toWalletType = $request->to_wallet;
        $amount = $request->amount;

        $fromWallet = $user->getWallet($fromWalletType);
        $toWallet = $user->getWallet($toWalletType);

        try {
            $arr = array_merge($validate->validated(), [
                "action" => "Transfer between wallets",
            ]);

            $fromWallet->transfer($toWallet, $amount, $arr);
            return get_success_response($fromWallet->only(['name', 'slug', 'meta', 'balance', 'decimal_places']), 'Transfer successful');
        } catch (\Exception $e) {
            return get_error_response($e->getMessage());
        }
    }

    public function transactions($walletType)
    {
        $user = auth()->user();
        $wallet = $user->getWallet($walletType);
        $transactions = $wallet->transactions()->select('*')->orderBy('created_at', 'desc')->paginate(20); //->makeHidden();

        return get_success_response($transactions, 'Transactions retrieved successfully');
    }

    public function getAllWallets()
    {
        $user = auth()->user();
        $wallets = $user->my_wallets();
        if ($wallets->isEmpty()) {
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
        }
        return get_success_response($wallets, 'All wallets retrieved successfully');
    }

    public function addNewWallet(Request $request)
    {
        try {
            $user = auth()->user();
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
            return get_error_response($th->getMessage());
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

            if ($account = BankAccounts::create($validate)) {
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
}
