
<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeeWaiverController;
use App\Http\Controllers\HostController;
use App\Http\Middleware\lang;
Route::middleware(['auth:sanctum'])->group(function () {

    Route::post('/start-chat', [ChatController::class, 'startChat']);
    Route::post('/send-message', [ChatController::class, 'sendMessage']);
    Route::get('/chat-messages/{chat_id}', [ChatController::class, 'getMessages']);

    });

