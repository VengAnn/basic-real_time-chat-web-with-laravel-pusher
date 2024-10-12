<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Events\MessageSent;

class ChatController extends Controller
{
    public function sendMessage(Request $request)
    {
        try {
            // Validate incoming request data, including timestamps from the client
            $request->validate([
                'to_id' => 'required|exists:users,id',
                'body' => 'required|string|max:1000',
                'attachment' => 'nullable|file|max:2048', // Optional file upload
                'created_at' => 'nullable|date_format:Y-m-d H:i:s', // Validate timestamp format
                'updated_at' => 'nullable|date_format:Y-m-d H:i:s'  // Validate timestamp format
            ]);

            // Create a new chat message
            $message = new ChatMessage();
            $message->from_id = Auth::id(); // ID of the authenticated user
            $message->to_id = $request->to_id; // ID of the recipient user
            $message->body = $request->body;

            // Handle file attachment if provided
            if ($request->hasFile('attachment')) {
                $message->attachment = $request->file('attachment')->store('attachments');
            }

            // Set timestamps if provided, or default to server time
            $message->created_at = $request->input('created_at') ?: now();
            $message->updated_at = $request->input('updated_at') ?: now();

            $message->save(); // Save the message to the database

            $msgJSon = $message->toJson();

            // Broadcast the message to Pusher
            broadcast(new MessageSent($msgJSon));

            return response()->json($message);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error sending message with ' . $e->getMessage()], 500);
        }
    }

    public function realTimeUpdateSeenOnChats(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'userId' => 'required|exists:users,id'
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Invalid user ID'], 400);
            }

            // Update the 'seen' status for messages where the current authenticated user is the recipient
            ChatMessage::where('from_id', $request->userId)
                ->where('to_id', Auth::id())
                ->where('seen', 0) // Only update unseen messages
                ->update(['seen' => 1]);

            $userId = $request->userId;
            $getNewUpdateData = ChatMessage::where('from_id', Auth::id())
                ->where('to_id', $userId)
                ->orWhere(function ($query) use ($userId) {
                    $query->where('from_id', $userId)
                        ->where('to_id', Auth::id());
                })
                ->orderBy('created_at', 'asc')
                ->get();

            $data = ['seen' => true, 'data' => $getNewUpdateData];
            $this->mySendNewBroadCast($data);

            return response()->json(['message' => 'Messages updated successfully', 'data' => $getNewUpdateData]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating seen status: ' . $e->getMessage()], 500);
        }
    }


    public function mySendNewBroadCast($data)
    {
        try {
            $array_msg = ['data' => $data];
            // convert to json string
            $msgJSon = json_encode($array_msg);

            broadcast(new MessageSent($msgJSon));
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error broadcasting message: ' . $e->getMessage()], 500);
        }
    }

    // Get messages between two users
    public function getMessages(Request $request)
    {
        try {
            // Validate that userId exists in users table
            $validator = Validator::make($request->all(), [
                'userId' => 'required|exists:users,id'
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Invalid user ID'], 400);
            }

            $userId = $request->userId;

            // Fetch messages where the authenticated user is either the sender or receiver
            $messages = ChatMessage::where('from_id', Auth::id())
                ->where('to_id', $userId)
                ->orWhere(function ($query) use ($userId) {
                    $query->where('from_id', $userId)
                        ->where('to_id', Auth::id());
                })
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json($messages);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching messages: ' . $e->getMessage()], 500);
        }
    }


}
