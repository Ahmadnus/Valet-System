<?php

namespace App\Services;

use App\Models\User;
use App\Models\Notification as ModelNotification;
use App\NotificationType;
use App\TopicType;
use App\TopicType as AppTopicType;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Laravel\Firebase\Facades\Firebase;

class NotificationService
{
    public Messaging $messaging;

    public function __construct()
    {
        $this->messaging = Firebase::messaging();
    }

    public function sendNotification(
        string $title,
        string $body,
        array $data = [],
        ?User $user = null,
        ?TopicType $topic = TopicType::GENERAL,
        NotificationType $type = NotificationType::REQUEST_PARKING_SERVICE
    ) {
        $payload = [
            'title' => $title,
            'body' => $body,
            'type' => $type->value,
            'extra' => json_encode($data),
        ];

        $firebaseNotification = Notification::create($title, $body);
        $message = CloudMessage::new()
            ->withNotification($firebaseNotification)
            ->withData($payload);

        try {
            if ($user) {
                $tokens = $user->fcmTokens()->pluck('token')->toArray();

                if (!empty($tokens)) {
                    // Send to all user's tokens (Multicast)
                    $this->sendMulticastNotification($title, $body, $tokens, $data, $type);
                } else {
                    Log::warning("User {$user->id} has no FCM tokens.");
                    return null;
                }
            } elseif ($topic) {
                // Send to topic
                $message = $message->withChangedTarget('topic', $topic->value);
                $this->messaging->send($message);
            }
            $response = $this->messaging->send($message);
            Log::info('FCM Response:', (array) $response);
            Log::info('Notification sent successfully.');
        } catch (\Throwable $th) {
            Log::error('Notification send failed: ' . $th->getMessage());
            return null;
        }

        // Optional: store notification in the database
        ModelNotification::create([
            'title' => $title,
            'body' => $body,
            'type' => $type->value,
            'data' => $data,
        ]);

        return true;
    }

    public function sendMulticastNotification(
        string $title,
        string $body,
        array $tokens,
        array $data = [],
        NotificationType $type = NotificationType::REQUEST_PARKING_SERVICE
    ) {
        if (empty($tokens)) return null;

        $payload = [
            'title' => $title,
            'body' => $body,
            'type' => $type->value,
            'extra' => json_encode($data),
        ];

        $firebaseNotification = Notification::create($title, $body);
        $message = CloudMessage::new()
            ->withNotification($firebaseNotification)
            ->withData($payload);

        try {
            $this->messaging->sendMulticast($message, $tokens);
            Log::info('Multicast notification sent.');
        } catch (\Throwable $th) {
            Log::error('Multicast send failed: ' . $th->getMessage());
        }

        ModelNotification::create([
            'title' => $title,
            'body' => $body,
            'type' => $type->value,
            'data' => $data,
        ]);
    }

  public function registerToken(array $tokens, AppTopicType $topic = AppTopicType::GENERAL)
    {
        $this->messaging->subscribeToTopic($topic->value, $tokens);
    }

    public function unregisterToken(array $tokens, AppTopicType $topic = AppTopicType::GENERAL)
    {
        $this->messaging->unsubscribeFromTopic($topic->value, $tokens);
    }
}
