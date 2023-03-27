<?php

namespace App\Http\Controllers\Chat;

use App\Http\Resources\Chat\ChatGResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Chat\ChatRoom;
use App\Models\Chat\Chat;
use App\Models\User;



class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function startChat(Request $request){
    
        date_default_timezone_set("America/Bogota");

        if($request->to_user_id == auth('api')->user()->id){
            return response()->json(["error"=> "No puedes iniciar un chat con tigo mismo"]);
        }
        $isExistRooms = ChatRoom::whereIn("first_user", [$request->to_user_id, auth('api')->user()->id])
                                    ->Wherein("second_user", [$request->to_user_id, auth('api')->user()->id])
                                    ->count();


        if($isExistRooms > 0){

            $chatRoom = ChatRoom::whereIn("first_user", [$request->to_user_id, auth('api')->user()->id])
            ->Wherein("second_user", [$request->to_user_id, auth('api')->user()->id])
            ->first();

            Chat::where('from_user_id', $request->to_user_id)
            ->where('chat_room_id', $chatRoom->id)
            ->where('read_at', NULL)
            ->update(['read_at' => now()]);

            $chats = Chat::where("chat_room_id", $chatRoom->id)->orderBy("created_at", "desc")->paginate(10);

            $data = [];
            $data["room_id"] = $chatRoom->id;
            $data["room_uniqd"] = $chatRoom->uniqd;
            $to_user = User::find($request->to_user_id);
            $data["user"]=[
                
                    "id"=>$to_user->id,
                    "full_name" => $to_user->name.' '.$to_user->surname,
                    "avatar"=> $to_user->avatar ? env("APP_URL")."storage/".$to_user->avatar : NULL,
            ];

            if (count($chats)> 0){
                foreach($chats as $key =>$chat){
                    $data["messages"][] =[
                            "id" => $chat->id,
                            "sender" => [
                                "id" => $chat -> FromUser -> id,
                                "full_name" => $chat-> FromUser->name.' '.$chat->FromUser->surname,
                                "avatar" => $chat->FromUser->avatar ? env("APP_URL")."storage/".$chat->FromUser->avatar : NULL,

                            ],
                            "message" => $chat->message,
                            //fillw
                            "read_at" => $chat->read_at,
                            "time" => $chat->created_at->diffForHummans(),
                            "created_at" => $chat -> created_at, 
                    ];
                }
            }else{
            $data["messages"][] = [];
            }

            $data["exist"] = 1;
            $data["last_page"] = $chat->lastPage();
            return response()->json($data);
        }else{
        
            $chatroom = ChatRoom::create([
                
                    "first_user" => auth()->user()->id,
                    "second_user" => $request->to_user_id,
                    "last_at" => now()->format("Y-m-d H:i:s.u"),
                    "uniqd"=> uniqid(),
            ]);

            $data = [];
            $data["room_id"] = $chatroom->id;
            $data["room_id"] = $chatroom->id;
            $data["room_uniqd"] = $chatroom->uniqd;
            $to_user = User::find($request->to_user_id);
            $data["user"]=[
                
                    "id"=>$to_user->id,
                    "full_name" => $to_user->name.' '.$to_user->surname,
                    "avatar"=> $to_user->avatar ? env("APP_URL")."storage/".$to_user->avatar : NULL,
            ];

            $data["messages"] = [];
            

            $data["exist"] = 0;
            $data["last_page"] = 1;
            return response()->json($data);
        }
                        
                        
    }

    public function sendMessageText(Request $request){
    
        date_default_timezone_set("America/Bogota");

        $request->request->add(["from_user_id" => auth('api')->user()->id]);
        $chat = Chat::create($$request->all());

        $chat->ChatRoom->update(["last_at"=> now()-> format("Y-m-d H:i:s.u")]);

        // NOTIFICAR AL SEGUNDO USUARIO Y HACER UN SUSH DE MENSAJE
        
        //NOTIFICAR A NUESTRA SALA DE CHAT
        // NOTIFICAMOS A LA SALA DE CHAT DEL SEGUNDO USUARIO
    }

    public function listMyChats (){
    
        $chatRooms = ChatRoom::where("first_user", auth('api')->user()->id)->orWhere("second_user", auth('api')->user()->id)
                ->orderBy("last_at", "desc")
                ->get();

       return response()->json([
        
            "chatrooms" => $chatRooms->map(function($item){
                return ChatGResource::make($item);
               
            }),
        ]);
    }

}
