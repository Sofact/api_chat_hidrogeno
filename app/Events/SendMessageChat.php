<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendMessageChat implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $chat;

    /**
     * Create a new event instance.
     */
    public function __construct($chat)
    {
        $this->chat = $chat;
        //
    }

    public function broadcastWith(){

     
       
        return [

            "id" => $this->chat->id,
            "sender" => [
                                "id" => $this->chat -> FromUser -> id,
                                "full_name" => $this->chat-> FromUser->name,
                                "avatar" =>$this->chat->FromUser->avatar ? env("APP_URL")."storage/".$this->chat->FromUser->avatar : NULL,

                            ],
                            "message" => $this->chat->message,
                            "read_at" => $this->chat->read_at,
                            "time" => $this->chat->created_at->diffForHumans(),
                            "created_at" => $this->chat -> created_at, 
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {

        return [
            new PrivateChannel('chat.room.'.$this->chat->ChatRoom->uniqd)
        ];
    }
}
