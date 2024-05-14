<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSeenEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $messageId;
    public $senderId;
    public $receiverId;
    public  $status;
    /**
     * Create a new event instance.
     */
    public function __construct($messageId,$senderId,$recevier,$status)
    {
        //
        $this->messageId = $messageId;
        $this->senderId = $senderId;
        $this->receiverId = $recevier;
        $this->status = $status;

    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return  new Channel('private-chat.'.$this->senderId);
    }

     public function broadcastAs()
    {
        return "message-seen";
    }
}
