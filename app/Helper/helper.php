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

if (!function_exists('generate_uuid')) {
    /**
     * @return string uniquid()
     * return string generate_uuid()
     */
    function generate_uuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

            // 32 bits for "time_low"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}