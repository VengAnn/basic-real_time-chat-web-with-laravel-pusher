<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\AuthController;
use App\Models\ChatMessage;
use App\Events\MessageSent;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('login');
});
Route::get('/register', function () {
    return view('register');
});
Route::get('/chat-page', function () {
    return view('chat_page');
});



Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');

Route::get('/get-all-users', [AuthController::class, 'getAllUsers']);

Route::middleware('auth')->group(function () {
    Route::post('/send-messages', [ChatController::class, 'sendMessage']);
    Route::get('/get-messages-between-two-user', [ChatController::class, 'getMessages']);

    Route::put('/update-real-time-seen', [ChatController::class, 'realTimeUpdateSeenOnChats']);
});


// working fine
Route::get('/test', function () {
    $message = new ChatMessage();
    $message->from_id = 2; // Set the sender's ID
    $message->to_id = 1;   // Set the recipient's user ID
    $message->body = 'Test message from the server';
    $message->attachment = null;
    $message->seen = false;

    //Convert the message to JSON
    $messageJson = $message->toJson();

    broadcast(event: new MessageSent($messageJson));
});