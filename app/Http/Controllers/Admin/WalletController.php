<?php

namespace App\Http\Controllers\Admin;

use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Towoju5\Wallet\Models\Wallet;

class WalletController extends Controller
{
    public function creditUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "_account_type" => "required",
            "user_id" => "required",
            "amount" => "required",
            "naration" => "required"
        ]);

        if($validator->fails()) {
            return back()->with('error', $validator->errors());
        }

        if(auth('admin')->id() == $request->user_id) {
            return back()->with('error', 'You can not credit your own wallet');
        }

        // find user wallet where _account_type
        $wallet = Wallet::where(['user_id' => $request->user_id, 'role' => $request->_account_type])->first();
        if($wallet) {
            $wallet->deposit($amount * 100, [
                "description" => $request->naration,
                "funded_by" => auth('admin')->user(),
                "is_admin" => true,
                "date_time" => now()
            ]);

            return back()->with('success', 'wallet credited successfully');
        }
    }

    public function debitUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "_account_type" => "required",
            "user_id" => "required",
            "amount" => "required",
            "naration" => "required"
        ]);

        if($validator->fails()) {
            return back()->with('error', $validator->errors());
        }

        if(auth('admin')->id() == $request->user_id) {
            return back()->with('error', 'You can not credit your own wallet');
        }

        // find user wallet where _account_type
        $wallet = Wallet::where(['user_id' => $request->user_id, 'role' => $request->_account_type])->first();
        if($wallet) {
            $wallet->withdrawal($amount * 100, [
                "description" => $request->naration,
                "funded_by" => auth('admin')->user(),
                "is_admin" => true,
                "date_time" => now()
            ]);

            return back()->with('success', 'wallet credited successfully');
        }
    }

    public function processWalletTransaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "_account_type" => "required",
            "user_id" => "required",
            "amount" => "required|numeric|min:1",
            "naration" => "required",
            "transaction_type" => "required|in:credit,debit"
        ]);

        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first());
        }

        if (auth('admin')->id() == $request->user_id) {
            return back()->with('error', 'You cannot modify your own wallet.');
        }

        $wallet = Wallet::where([
            'user_id' => $request->user_id,
            'role' => $request->_account_type
        ])->first();

        if (!$wallet) {
            return back()->with('error', 'Wallet not found.');
        }

        try {
            if ($request->transaction_type === 'credit') {
                $wallet->deposit($request->amount * 100, [
                    "description" => $request->naration,
                    "funded_by" => auth('admin')->user(),
                    "is_admin" => true,
                    "date_time" => now()
                ]);
                return back()->with('success', 'Wallet credited successfully.');
            } elseif ($request->transaction_type === 'debit') {
                $wallet->withdraw($request->amount * 100, [
                    "description" => $request->naration,
                    "funded_by" => auth('admin')->user(),
                    "is_admin" => true,
                    "date_time" => now()
                ]);
                return back()->with('success', 'Wallet debited successfully.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Transaction failed: ' . $e->getMessage());
        }
    }

}
