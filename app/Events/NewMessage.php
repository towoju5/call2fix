<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Ably\AblyRest;

class NewMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    private $ably;

    // Constructor
    public function __construct(Message $message)
    {
        $this->message = $message;
        $this->ably = new AblyRest(config('services.ably.key'));
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
            'message' => $this->message->content,
            'sender' => $this->message->user->name,
            'timestamp' => $this->message->created_at->toDateTimeString(),
        ];
    }
}
