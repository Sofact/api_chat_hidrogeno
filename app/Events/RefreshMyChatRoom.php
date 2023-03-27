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
use app\Models\Chat\ChatRoom;
use app\Http\Resources\Chat\ChatGResource;

class RefreshMyChatRoom implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $to_user_id;

    /**
     * Create a new event instance.
     */
    public function __construct($to_user_id)
    {
        $this->to_user_id = $to_user_id;
        //
    }

    public function broadcastWith(){
        
        $chatRooms = ChatRoom::where("first_user",  $this->to_user_id)->orWhere("second_user",  $this->to_user_id)
                                ->orderBy("last_at", "desc")
                                ->get();

        return [
            "chatrooms" => $chatRooms->map(function($item){
                return ChatGResource::make($item);
               
            }),
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        //validar si es correcta la utilizacion del to_user_id
        
        return [
            new PrivateChannel('chat.refresh.room.'.$this->to_user_id)
        ];
    }
}
