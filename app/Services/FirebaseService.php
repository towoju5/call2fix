<?php

namespace  App\Services;

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
        $message = CloudMessage::withTarget('token', $token)
                    // ->withNotification(Notification::create($title, $body)) 
                    ->withNotification(['title' => $title, 'body' => $body])
                    ->withData($data);

        $send = $this->messaging->send($message);

        return $send;
    }
}