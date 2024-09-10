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
                    if (!$user->bank_account) {
                        // generate deposit account for customer
                        $account_info = BankAccounts::generateAccount($user->id);
                        return response()->json($account_info);
                    }

                    $account_info = $user->bank_account;

                    if ($account_info) {
                        return get_success_response($account_info, 'Account info retrieved successfully');
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
        ]);

        if ($validate->fails()) {
            return get_error_response("Validation Error", $validate->errors());
        }

        $user = $request->user();
        $wallet = $user->getWallet($walletType);
        $amount = $request->amount;

        try {
            $transaction = $wallet->withdraw($amount, $request->toArray());
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
            return get_success_response($fromWallet->only(['name', 'slug','meta','balance', 'decimal_places']), 'Transfer successful');
        } catch (\Exception $e) {
            return get_error_response($e->getMessage());
        }
    }

    public function transactions($walletType)
    {
        $user = auth()->user();
        $wallet = $user->getWallet($walletType);
        $transactions = $wallet->transactions()->select('type', 'amount', 'meta')->orderBy('created_at', 'desc')->paginate(20); //->makeHidden();

        return get_success_response($transactions, 'Transactions retrieved successfully');
    }

    public function getAllWallets()
    {
        $user = auth()->user();
        $wallets = $user->my_wallets();
        return get_success_response($wallets, 'All wallets retrieved successfully');
    }

    public function addNewWallet(Request $request)
    {
        try {
            $user = auth()->user();
            $walletName = $request->input('wallet_name');
            $walletSlug = $request->input('wallet_slug');
            
            $wallet = $user->createWallet([
                'name' => $walletName,
                'slug' => $walletSlug,
            ]);

            return get_success_response($wallet, 'New wallet added successfully');
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage());
        }
    }

}
