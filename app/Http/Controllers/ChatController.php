<?php

namespace App\Http\Controllers;

use App\app\Traits\ApiResponse;
use App\Events\sendMessage;
use App\Models\Message;
use Illuminate\Http\Request;
use App\Models\Chat;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Chat\SendMessageRequest;
use App\Http\Requests\Chat\StartChatRequest;

use App\Http\Resources\ChatResource;
use App\Http\Resources\Chat\MessageResource;
use App\Services\ChatService;

class ChatController extends Controller
{
    use ApiResponse;

    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    public function startChat(StartChatRequest $request)
    {
        $chat = $this->chatService->startChat($request->validated());

        return $this->successResponse('Chat started successfully', new ChatResource($chat));
    }

    public function sendMessage(SendMessageRequest $request)
    {
        $message = $this->chatService->sendMessage($request->validated());

        return $this->successResponse('Message sent successfully', new MessageResource($message));
    }

    public function getMessages($chat_id)
    {
        $messages = $this->chatService->getMessages($chat_id);

        return $this->successResponse('Messages retrieved successfully', MessageResource::collection($messages));
    }

}
