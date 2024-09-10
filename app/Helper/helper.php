<?php

use App\Models\Settings;
use App\Models\User;
// use Storage;

if (!function_exists('get_success_response')) {
    function get_success_response($data, $message = 'Success', $code = 200)
    {
        return response()->json([
            'status' => true,
            'status_code' => $code,
            'message' => $message,
            'data' => $data
        ], $code);
    }
}

if (!function_exists('get_error_response')) {
    function get_error_response($message, $errors = [], $code = 400)
    {
        return response()->json([
            'status' => false,
            'status_code' => $code,
            'message' => $message,
            'errors' => $errors instanceof \Illuminate\Support\MessageBag ? $errors : new \Illuminate\Support\MessageBag($errors)
        ], $code);
    }
}

if (!function_exists('credit_user')) {
    function credit_user($userId, $amount)
    {
        $walletType = 'ngn';
        $user = User::find($userId);
        $wallet = $user->getWallet($walletType);

        if ($wallet && ($transaction = $wallet->deposit($amount))) {
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


if (!function_exists('fcm')) {
    function fcm($title, $body, $deviceId = null)
    {
        if (null === $deviceId) {
            $firebaseToken = User::whereNotNull('device_token')->pluck('device_token')->all();
        } else {
            $firebaseToken = $deviceId;
        }

        $SERVER_API_KEY = 'BFW7n7Hpu1w8Y7c3zp8vMnuAh1i2n2tH0vMiGZrZGemkt-OodDvOR3Gao0Z8hg47lRrcgpwY5e8Vo8xoTCCoB_s';

        $data = [
            "registration_ids" => $firebaseToken,
            "notification" => [
                "title" => $title,
                "body" => $body,
            ]
        ];
        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);

        dd($response);
    }
}


if (!function_exists('get_settings_value')) {
    function get_settings_value($key)
    {
        $setting = Settings::where('key', $key)->first();
        if ($setting) {
            return $setting->value;
        }
        return null;
    }
}

if (!function_exists('save_media')) {
    function save_media($file)
    {
        if (is_file($file)) {
            // Store the file in the 'spaces' disk
            $path = Storage::disk('spaces')->put(auth()->id(),$file, [
                'visibility' => 'public',
                'CacheControl' => 'max-age=31536000',
            ]);

            // Return the file URL
            $url = Storage::disk('spaces')->url($path);

            return str_replace("https://lon1.digitaloceanspaces.com/", "https://alphamead.lon1.digitaloceanspaces.com/", $url);
        }

        return false;
    }
}