<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TransactionRecordsController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $transactions = $user->transactions();
            return get_success_response($transactions, 'Transactions retrieved successfully');
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), $th->getTrace(), 500);
        }
    }

    public function show(Request $request, $txnId)
    {
        try {
            $user = $request->user();
            $transactions = $user->transactions()->whereId($txnId)->first();
            return get_success_response($transactions, 'Transactions retrieved successfully');
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), $th->getTrace(), 500);
        }
    }
}
