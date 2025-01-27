<?php

namespace  App\Services;

use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;


class FirebaseService {
    protected $messaging;

    public function __construct()
    {
        $serviceAccountPath = storage_path('app/json/call2fix-54a46-firebase-adminsdk-x7uej-e03371a1df.json');
        $factory = (new Factory())->withServiceAccount($serviceAccountPath);
        $this->messaging = $factory->createMessaging();
    }


    public function sendNotification($title, $body, $token, $data = [])
    {
        // $cloudMessage = CloudMessage::fromArray([
        //     'token' => $token,
        //     'notification' => [
        //         'title' => $title,
        //         'body' => $body
        //     ],
        //     'data' => $data
        // ]);

        $message = CloudMessage::withTarget('token', $token)
                    // ->withNotification(Notification::create($title, $body)) 
                    ->withNotification(['title' => $title, 'body' => $body])
                    ->withData($data);

        $send = $this->messaging->send($message);
        Log::info("cloud message update", ['result' => $send, 'cloudMessage' => $cloudMessage]);
        return $send;
    }
}


// [program:laravel-reverb]
// command=php /var/www/call2fix-app.alphamead.com/artisan reverb:start --tries=3
// autostart=true
// autorestart=true
// user=www-data
// redirect_stderr=true
// stdout_logfile=/var/log/laravel-reverb.log
// stderr_logfile=/var/log/laravel-reverb.err.log


// [program:laravel-queue]
// command=php /var/www/call2fix-app.alphamead.com/artisan queue:work --tries=3
// autostart=true
// autorestart=true
// user=www-data
// redirect_stderr=true
// stdout_logfile=/var/log/laravel-queue.log
// stderr_logfile=/var/log/laravel-queue.err.log