<?php

use App\Models\Settings;
use App\Models\User;
use App\Services\FirebaseService;
use Creatydev\Plans\Models\PlanModel;
use Google\Auth\Credentials\ServiceAccountCredentials;
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

if (!function_exists('active_role')) {
    function active_role()
    {
        if (auth()->guest()) {
            return null;
        }
        $request = request();
        return $request->user()->current_role ?? $request->user()->account_type;
    }
}

if (!function_exists('get_default_currency')) {
    function get_default_currency($userId, $default = 'ngn')
    {
        return $default;
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


if (!function_exists('getGoogleAccessToken')) {
    /**
     * Returns access Token for Google Firebase
     * @return mixed
     */
    function getGoogleAccessToken()
    {
        // Path to the service account JSON key file
        $serviceAccountFilePath = storage_path('call2fix.json');

        $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];

        // Create credentials object
        $credentials = new ServiceAccountCredentials(['*'], $serviceAccountFilePath);

        // Fetch the access token
        $credentials->fetchAuthToken();
        $accessToken = $credentials->getLastReceivedToken();

        // Return the access token string
        return $accessToken['access_token'];
    }
}
;

if (!function_exists('fcm')) {
    function fcm($title, $body, string $deviceId = null, $data = [])
    {
    $firebase = new FirebaseService();
    $response = $firebase->sendNotification($title, $body, $deviceId, $data);
    \Log::info("FCM response", ['response' => $response]);
    return $response;
    }
}


if (!function_exists('get_settings_value')) {
    function get_settings_value($key, $default = null)
    {
        $setting = Settings::where('key', $key)->first();
        if ($setting) {
            return $setting->value;
        }
        return $default;
    }
}

if (!function_exists('save_media')) {
    function save_media($file)
    {
        if (is_file($file)) {
            // Store the file in the 'spaces' disk

            // Define a unique file name and path
            $fileName = uniqid() . '_' . $file->getClientOriginalName();
            $path = auth()->id() . '/' . $fileName;

            // Store the file in DigitalOcean Spaces
            Storage::disk('spaces')->put($path, file_get_contents($file), [
                'visibility' => 'public',
                'CacheControl' => 'max-age=31536000',
            ]);

            if (!empty($path)) {
                // Return the file URL
                $url = Storage::disk('spaces')->url($path);

                return $url;
            }
        }

        return false;
    }
}


if (!function_exists('get_free_plan')) {
    function get_free_plan()
    {
        $plan = PlanModel::wherePrice(0)->first();
        if ($plan) {
            // subscribe user to plan 
            $user = auth()->user();
            $user->subscribeTo($plan, 30, true);
            return $user->activeSubscription();
        }
    }
}
