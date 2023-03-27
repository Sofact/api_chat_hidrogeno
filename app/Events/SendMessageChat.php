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

         $chater = $this->chat;
        echo($chater);
       
        return [

            //debe verificarse si es correcto que se introduzca aqui la variable chater

            "id" => $this->$chater->id,
            "sender" => [
                                "id" => $this->$chater -> FromUser -> id,
                                "full_name" => $this->$chater-> FromUser->name.' '.$this->$chater->FromUser->surname,
                                "avatar" =>$this->$chater->FromUser->avatar ? env("APP_URL")."storage/".$this->$chater->FromUser->avatar : NULL,

                            ],
                            "message" => $this->$chater->message,
                            "read_at" => $this->$chater->read_at,
                            "time" => $this->$chater->created_at->diffForHummans(),
                            "created_at" => $this->$chater -> created_at, 
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
