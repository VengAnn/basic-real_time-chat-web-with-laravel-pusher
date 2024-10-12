<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct($message)
    {
        $this->message = $message;
    }


    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        // // Convert the JSON string back to an object
        // $messageObject = json_decode($this->message);
        // $messageObject->to_id;
        return new Channel('chatApp');
    }


    /**
     * Event name for broadcasting
     * @return string
     */
    public function broadcastAs()
    {
        return 'my-chat-message';
    }

    /**
     * Get the data to broadcast.
     * Data sending
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'message' => $this->message
        ];
    }

}
