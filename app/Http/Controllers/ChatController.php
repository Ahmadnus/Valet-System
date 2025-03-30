<?php

namespace App\Http\Controllers;

use App\Events\sendMessage;
use App\Models\Message;
use Illuminate\Http\Request;
use App\Models\Chat;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    public function startChat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'agent_id' => 'required|exists:users,id',
            'customer_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $chat = Chat::create([
            'agent_id' => $request->agent_id,
            'customer_id' => $request->customer_id
        ]);

        return response()->json(['success' => true, 'chat' => $chat]);
    }
    public function sendMessage(Request $request)
{
    $validator = Validator::make($request->all(), [
        'chat_id' => 'required|exists:chats,id',
        'user_id' => 'required|exists:users,id',
        'content' => 'required|string'
    ]);

    if ($validator->fails()) {
        return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
    }

    $message = Message::create([
        'chat_id' => $request->chat_id,
        'user_id' => $request->user_id,
        'content' => $request->content
    ]);


    broadcast(new sendMessage($message))->toOthers();

    return response()->json(['success' => true, 'message' => $message]);
}
public function getMessages($chat_id)
{
    $messages = Message::where('chat_id', $chat_id)->with('user')->orderBy('created_at', 'asc')->get();

    return response()->json(['success' => true, 'messages' => $messages]);
}
}
