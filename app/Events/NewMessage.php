<?php

namespace App\Events;

use App\Models\Message;
use App\Notifications\NewMessageNotification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use Log;

class NewMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
        $this->sendNotificationToCustomer();
    }

    public function broadcastOn()
    {
        return new PresenceChannel('chat.' . $this->message->chat_id);
    }

    private function sendNotificationToCustomer()
    {
        Log::info(json_encode($this->message->chat));
        // $customer = $this->message->chat->customer;
        // Notification::send($customer, new NewMessageNotification($this->message));
    }
}
