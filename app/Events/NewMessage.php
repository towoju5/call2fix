<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    // Constructor
    public function __construct(Message $message, $user)
    {
        $this->message = $message;
        $this->user = $user;
    }

    // Broadcast on the channel
    public function broadcastOn()
    {
        // Channel can be dynamic based on the chat ID or a static name
        return new Channel('chat.' . $this->message->chat_id);
    }

    // Broadcast message data
    public function broadcastWith()
    {
        return [
            'message' => $this->message,
            'sender' => auth()->user(),
            'user' => auth()->user(),
            'timestamp' => $this->message->created_at->toDateTimeString(),
        ];
    }
}
