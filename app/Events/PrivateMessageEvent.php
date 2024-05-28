<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrivateMessageEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $senderId;
    public $recipientUserId;
    public $message;
    public $status;
    public $attachment;

    public function __construct($senderId,$recipientUserId, $message,$status,$attachment)
    {
        $this->recipientUserId = $recipientUserId;
        $this->message = $message;
        $this->senderId = $senderId;
        $this->status = $status;
        $this->attachment = $attachment;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return new Channel('private-chat.' . $this->recipientUserId);
        
    }

    public function broadcastAs()
    {
        return 'private-message';
    }

   
}
