<?php
use App\Models\User;

if (!function_exists('get_success_response')) {
    function get_success_response($data, $message = 'Success', $code = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $code);
    }
}

if (!function_exists('get_error_response')) {
    function get_error_response($message, $errors = [], $code = 400)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors
        ], $code);
    }
}

if (!function_exists('credit_user')) {
    function credit_user($userId, $amount)
    {
        $walletType = 'ngn';
        $user = User::find($userId);
        $wallet = $user->getWallet($walletType);

        if($wallet && ($transaction = $wallet->deposit($amount))) {
            return $transaction;
        }

        return false;
    }
}