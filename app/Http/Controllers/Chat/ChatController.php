<?php

namespace App\Http\Controllers\Chat;

use App\Events\RefreshMyChatRoom;
use App\Events\SendMessageChat;
use App\Http\Resources\Chat\ChatGResource;
use App\Http\Controllers\Controller;
use App\Models\Chat\ChatGroup;
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


    public function startGroupChat(Request $request){
    
        date_default_timezone_set("America/Bogota");

        $chatGroup = ChatGroup::create([
                
            "name" =>  $request->nombreGrupo,
            "uniqd"=> uniqid(),
    ]);

    $idgrupo = ChatGroup::Where("name","=", [$request->nombreGrupo])
                                ->first();

    $chatroom = ChatRoom::create([
                
        "first_user" => auth()->user()->id,
        "second_user" => 0,
        "chat_group_id" => $idgrupo->id,
        "last_at" => now()->format("Y-m-d H:i:s.u"),
        "uniqd"=> uniqid(),
]);

    }

  

    public function startChat(Request $request){

    
        date_default_timezone_set("America/Bogota");

        if($request->to_user_id == auth('api')->user()->id){
            return response()->json(["error"=> "No puedes iniciar un chat con tigo mismo"]);
        }

        $isExistRooms = ChatRoom::whereIn("first_user", [$request->to_user_id, auth('api')->user()->id])
                        ->Wherein("chat_group_id", [$request->to_user_id])
                        ->count();
        if($isExistRooms == 0){
        $isExistRooms = ChatRoom::whereIn("first_user", [$request->to_user_id, auth('api')->user()->id])
                        ->Wherein("second_user", [$request->to_user_id, auth('api')->user()->id])
                        ->count();
        }
                //        echo "Existe????::::", $isExistRooms, "after exist";
            
        if($isExistRooms > 0){

            $isGroup = ChatRoom::Wherein("chat_group_id", [$request->to_user_id])
                                ->count();

            if($isGroup){

                $chatRoom = ChatRoom::whereIn("first_user", [$request->to_user_id, auth('api')->user()->id])
                                ->Wherein("chat_group_id", [$request->to_user_id, auth('api')->user()->id])
                                ->first();

                Chat::where('from_user_id', $request->to_user_id)
                    ->where('chat_group_id', $request->to_user_id)
                    ->where('read_at', NULL)
                    ->update(['read_at' => now()]);
            
            $chats = Chat::where("chat_group_id",  $request->to_user_id)->orderBy("created_at", "desc")->paginate(10);
                
            
            }else{

            $chatRoom = ChatRoom::whereIn("first_user", [$request->to_user_id, auth('api')->user()->id])
                                ->Wherein("second_user", [$request->to_user_id, auth('api')->user()->id])
                                ->first();

            Chat::where('from_user_id', $request->to_user_id)
                ->where('chat_room_id', $chatRoom->id)
                ->where('read_at', NULL)
                ->update(['read_at' => now()]);

                $chats = Chat::where("chat_room_id", $chatRoom->id)->orderBy("created_at", "desc")->paginate(10);
            
                }

           

            $data = [];
            $data["room_id"] = $chatRoom->id;
            $data["room_uniqd"] = $chatRoom->uniqd;

        

            $to_user = User::find($request->to_user_id);
            
            if(!$to_user){
                $to_user = ChatGroup::find($request->to_user_id);

           }

            $data["user"]=[
                
                    "id"=>$to_user->id,
                    "full_name" => $to_user->name,
                    "avatar"=> $to_user->usr_avatar ? $to_user->usr_avatar : NULL,
            ];
    

            if (count($chats)> 0){
                foreach($chats as $key =>$chat){
                 //   echo "El valor del Chat en el forantes:::", $chat;
                    $data["messages"][] =[
                            "id" => $chat->id,
                            "sender" => [
                                "id" => $chat -> FromUser-> id,
                                "full_name" => $chat-> FromUser->name,
                                "avatar" => $chat->FromUser->usr_avatar ? $chat->FromUser->usr_avatar : "non-avatar.png",

                            ],
                            
                            "message" => $chat->message,
                            //fillw
                            "read_at" => $chat->read_at,
                            "time" => $chat->created_at->diffForHumans(),
                            "created_at" => $chat -> created_at, 
                    ];
              //      echo "El valor del Chat en el for:::", $chat;
                }
            }else{
            $data["messages"] = [];
            }

            $data["exist"] = 1;
            $data["last_page"] = $chats->lastPage();
            return response()->json($data);
        }else{
        
          //  echo "en el else????::::", $isExistRooms, "after exist";
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

            if(!$to_user){
                $to_user = ChatGroup::find($request->to_user_id);

           }

            $data["user"]=[
                
                    "id"=>$to_user->id,
                    "full_name" => $to_user->name,
                    "avatar"=> $to_user->usr_avatar ? env("APP_URL")."storage/".$to_user->usr_avatar : "non-avatar.png",
            ];

            $data["messages"] = [];
            

            $data["exist"] = 0;
            $data["last_page"] = 1;
            return response()->json($data);
        }
                        
           
    }

    public function sendMessageText(Request $request){

    
        date_default_timezone_set("America/Bogota");

        $isGroup = ChatRoom::Wherein("id", [$request->chat_room_id])
                           // ->WhereNotNull("chat_group_id")
                            ->first();
        
        if($isGroup){
            $request->request->add(["chat_group_id" => $isGroup->chat_group_id]);
        }                            
       

        $request->request->add(["from_user_id" => auth('api')->user()->id]);
        $chat = Chat::create($request->all());

        $chat->ChatRoom->update(["last_at"=> now()-> format("Y-m-d H:i:s.u")]);

        // NOTIFICAR AL SEGUNDO USUARIO Y HACER UN pUSH DE MENSAJE
        broadcast (new SendMessageChat($chat));
        broadcast (new RefreshMyChatRoom($request->to_user_id));
        broadcast (new RefreshMyChatRoom(auth('api')->user()->id));
      //  broadcast(new SendMessageChat($chat));
     //   broadcast(new RefreshMyChatRoom($chat->Chatrom));
        //NOTIFICAR A NUESTRA SALA DE CHAT
        // NOTIFICAMOS A LA SALA DE CHAT DEL SEGUNDO USUARIO

        return response()->json(["message"=> 200]);
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
