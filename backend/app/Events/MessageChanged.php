<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;


class MessageChanged implements ShouldBroadcast
{
    use SerializesModels;

    public $messageId, $sessionId, $action, $newMessage;

    public function __construct($messageId, $sessionId, $action, $newMessage = null)
    {
        $this->messageId = $messageId;
        $this->sessionId = $sessionId;
        $this->action = $action;
        $this->newMessage = $newMessage;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('chat.' . $this->sessionId);
    }

    public function broadcastWith()
    {
        return [
            'message_id' => $this->messageId,
            'action'     => $this->action,
            'message'    => $this->newMessage,
        ];
    }

    public function broadcastAs()
    {
        return 'MessageChanged';
    }
}
