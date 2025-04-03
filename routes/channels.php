<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Chat;
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
/*Broadcast::channel('chat.{chat_id}', function ($user, $chat_id) {
    // Check if the user is part of the chat
    $chat = Chat::findOrFail($chat_id);

    // Check if the user is either the agent or the customer in this chat
    return $chat->agent_id === $user->id || $chat->customer_id === $user->id;
});/

//Broadcast::channel('trucking.{carId}', function ($user, $carId) {

//    return $user->hasCar($carId);

//}, ['middleware' => ['auth:sanctum']]);*/