<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseNotificationService;
use App\services\NotificationService;

class NotificationController extends Controller
{
    protected $firebaseService;

    public function __construct(NotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function send(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'body' => 'required|string',
            'token' => 'required|string'
        ]);

        $this->firebaseService->sendNotification(
            $request->title,
            $request->body,
            $request->token,
            $request->data ?? []
        );

        return response()->json(['message' => 'Notification sent!']);
    }
}
