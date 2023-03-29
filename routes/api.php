<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JWTController;
use App\Http\Controllers\User\ProfileUserController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\Chat\ChatController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::group(['middleware' => 'api'], function($router){
    Route::post('/register', [JWTController::class, 'register']);
    Route::post('/login', [JWTController::class, 'login']);
    Route::post('/logout', [JWTController::class, 'logout']);
    Route::post('/refresh', [JWTController::class, 'refresh']);
    Route::post('/profile', [JWTController::class, 'profile']);
    Route::get('/users/contact', [ProfileUserController::class, 'contactUsers']);
    Route::get('/agenda', [AgendaController::class, 'getAllAgenda']);
    Route::post('/start-chat', [ChatController::class, 'startChat']);
    
});

Route::group(['prefix' => 'chat'], function($router){

   // Route::post('/start-chat', [ChatController::class, 'startChat']);
    Route::post('/list-my-chat-room', [ChatController::class, 'listMyChats']);
    Route::post('/send-message-txt', [ChatController::class, 'sendMessageText']);

});

