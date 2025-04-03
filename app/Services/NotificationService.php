<?php

namespace App\services;

use App\Models\User;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use App\Models\Notification as ModelNotification;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Create a new class instance.
     */

     public Messaging $messaging;

     public function __construct()
     {
         $this->messaging = Firebase::messaging();
     }

     public function sendNotification(string $title, string $body, ?User $user = null, array $data = [], string $topic = 'all', $type = null)
     {
         $toSend = [
             'title' => $title,
             'body' => $body,
             'type' => $type ?? 'general',
             'extra' => json_encode($data),
         ];

         $notification = Notification::create($title, $body);
         $message = CloudMessage::new()
             ->withNotification($notification)
             ->withData($data);

         if ($user && $user->fcm_token) {
             $message = $message->withChangedTarget('token', $user->fcm_token);
         } else {
             $message = $message->withChangedTarget('topic', $topic);
         }

         try {
             $response = $this->messaging->send($message);
             Log::info('Notification sent successfully.');
         } catch (\Throwable $th) {
             Log::error('Notification send failed: ' . $th->getMessage());
             return null;
         }

         $model = ModelNotification::create([
             'title' => $title,
             'body' => $body,
             'type' => $type,
         ]);

         if ($user) {
             $user->notifications()->attach($model->id);
         } elseif (strtolower($topic) === 'all') {
             $usersId = User::pluck('id')->toArray();
             $model->users()->attach($usersId);
         }

         return $response ?? true;
     }





     public function registarToken(array $tokens, string $topic = 'all')
     {
         $this->messaging->subscribeToTopic($topic, $tokens);
     }

     public function registarTokenToTopic(array $tokens, string $topic = 'all')
     {
         $this->messaging->subscribeToTopic($topic, $tokens);
     }

     public function unRegistarToken(array $tokens, string $topic = 'all', User $user = null)
     {
         $this->messaging->unsubscribeFromTopic($topic, $tokens);
     }


    }

