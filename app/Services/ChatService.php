<?php

namespace App\Services;
use App\Events\SendMessage;
use App\Http\Resources\Chat\MessageResource;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
class ChatService
{
    /**
     * Create a new class instance.
     */
    public function startChat(array $data)
    {
        $user = Auth::user();

        if (!$user) {
            Log::error("Unauthorized: No authenticated user found.");
            abort(401, 'Unauthorized.');
        }

        if ($user->hasRole('valet_agent')) {
            $data['agent_id'] = $user->id;
        } elseif ($user->hasRole('customer')) {
            $data['customer_id'] = $user->id;
        } else {
            Log::warning("User ID {$user->id} tried to start a chat without a valid role.");
            abort(403, 'Unauthorized action.');
        }


        $existingChat = Chat::where([
            'agent_id' => $data['agent_id'],
            'customer_id' => $data['customer_id']
        ])->first();

        if ($existingChat) {
            Log::info("Chat already exists between Agent ID {$data['agent_id']} and Customer ID {$data['customer_id']}");
            return $existingChat;
        }


        $chat = Chat::create([
            'agent_id' => $data['agent_id'],
            'customer_id' => $data['customer_id'],
        ]);

        Log::info("New chat started: Chat ID {$chat->id} between Agent ID {$data['agent_id']} and Customer ID {$data['customer_id']}");

        return $chat;
    }

    public function sendMessage(array $data)
    {
        $chat = Chat::find($data['chat_id']);

        if (!$chat) {
            Log::warning("Message sending failed: Chat ID {$data['chat_id']} not found.");
            abort(404, 'Chat not found.');
        }


        $userId = Auth::id();

        if (!$userId) {
            Log::error("Unauthorized: No authenticated user found.");
            abort(401, 'Unauthorized.');
        }


        if (!in_array($userId, [$chat->agent_id, $chat->customer_id])) {
            Log::warning("Unauthorized message attempt by User ID {$userId} in Chat ID {$chat->id}");
            abort(403, 'Unauthorized to send messages in this chat.');
        }


        $message = Message::create([
            'chat_id' => $data['chat_id'],
            'user_id' => $userId,
            'content' => $data['content'],
        ]);


        broadcast(new SendMessage($message))->toOthers();

        Log::info("Message sent in Chat ID {$chat->id} by User ID {$data['user_id']}");

        return $message;
    }

    public function getMessages($chat_id)
    {
        $chat = Chat::find($chat_id);

        if (!$chat) {
            Log::warning("Chat messages fetch failed: Chat ID {$chat_id} not found.");
            abort(404, 'Chat not found.');
        }


        $userId = Auth::id();
        if (!in_array($userId, [$chat->agent_id, $chat->customer_id])) {
            Log::warning("Unauthorized chat access attempt by User ID {$userId} in Chat ID {$chat_id}");
            abort(403, 'Unauthorized to view messages in this chat.');
        }

        $messages = Message::where('chat_id', $chat_id)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        Log::info("Messages retrieved for Chat ID {$chat_id} by User ID {$userId}");

        return MessageResource::collection($chat->messages);
    }
}
