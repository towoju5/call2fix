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

            if($validate->fails()) {
                return get_error_response($validate->errors());
            }

            $user = User::find(auth()->id());

            if($request->payment_mode == 'bank_transfer') {
                // check if user has bank account allocated already
                if(!$user->bank_account) {
                    // generate deposit account for customer
                    $account_info = BankAccounts::generateAccount($user->id);
                }

                $account_info = $user->getAccountInfo($user->id);

                if($account_info) {
                    return get_success_response($account_info, 'Account info retrieved successfully');
                }

                return get_error_response(['error' => 'Unable to retrieve bank account'], 'Unable to retrieve bank account');
            } else if($request->payment_mode == 'credit_card') {
                // generate a checkout url
                $paystack = new Paystack();
                $data = [
                    'amount' => $request->amount,
                ];
                $checkoutUrl = $paystack->makePaymentRequest($data);
                return response()->json(['url' => $checkoutUrl]);
                // ->redirectNow();
            }

        
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage());
        }
    }


    public function processDeposit(Request $request)
    {
        $walletType = 'ngn';
        $user = $request->user();
        $wallet = $user->getWallet($walletType);
        $amount = $request->amount;

        try {
            $transaction = credit_user($user->id, $amount);
            return get_success_response($transaction, 'Deposit successful');
        } catch (\Exception $e) {
            return get_error_response($e->getMessage());
        }
    }

    public function withdraw(Request $request, $walletType)
    {
        $user = $request->user();
        $wallet = $user->getWallet($walletType);
        $amount = $request->amount;

        try {
            $transaction = $wallet->withdraw($amount);
            return get_success_response($transaction, 'Withdrawal successful');
        } catch (\Exception $e) {
            return get_error_response($e->getMessage());
        }
    }

    public function balance($walletType)
    {
        $user = auth()->user();
        $wallet = $user->getWallet($walletType);
        $balance = $wallet->balance;

        return get_success_response(['balance' => $balance], 'Balance retrieved successfully');
    }

    public function transfer(Request $request)
    {
        $user = $request->user();
        $fromWalletType = $request->from_wallet;
        $toWalletType = $request->to_wallet;
        $amount = $request->amount;

        $fromWallet = $user->getWallet($fromWalletType);
        $toWallet = $user->getWallet($toWalletType);

        try {
            $fromWallet->transfer($toWallet, $amount);
            return get_success_response(null, 'Transfer successful');
        } catch (\Exception $e) {
            return get_error_response($e->getMessage());
        }
    }

    public function transactions($walletType)
    {
        $user = auth()->user();
        $wallet = $user->getWallet($walletType);
        $transactions = $wallet->transactions()->orderBy('created_at', 'desc')->get();

        return get_success_response(['transactions' => $transactions], 'Transactions retrieved successfully');
    }

    public function getAllWallets()
    {
        $user = auth()->user();
        $wallets = $user->wallets()->with('balance')->get();

        // $formattedWallets = $wallets->map(function ($wallet) {
        //     return [
        //         'id' => $wallet->id,
        //         'name' => $wallet->name,
        //         'slug' => $wallet->slug,
        //         'balance' => $wallet->balance,
        //     ];
        // });

        return get_success_response(['wallets' => $wallets], 'All wallets retrieved successfully');
    }

}
