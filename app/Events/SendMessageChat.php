<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendMessageChat implements ShouldBroadcastNow
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

         $chat = $this->chat;
        echo "En el broadcast with",$chat;
       
        return [

            //debe verificarse si es correcto que se introduzca aqui la variable chater

            "id" => $this->$chat->id,
            "sender" => [
                                "id" => $this->$chat -> FromUser -> id,
                                "full_name" => $this->$chat-> FromUser->name.' '.$this->$chat->FromUser->surname,
                                "avatar" =>$this->$chat->FromUser->avatar ? env("APP_URL")."storage/".$this->$chat->FromUser->avatar : NULL,

                            ],
                            "message" => $this->$chat->message,
                            "read_at" => $this->$chat->read_at,
                            "time" => $this->$chat->created_at->diffForHummans(),
                            "created_at" => $this->$chat -> created_at, 
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $chater = $this->chat;
        return [
            new PrivateChannel('chat-room.'.$this->$chater->ChatRoom->uniqid)
        ];
    }
}
