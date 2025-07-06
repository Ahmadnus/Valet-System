<?php

namespace App\Services;

use App\Models\Notification as ModelNotification;
use App\NotificationType;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Kreait\Laravel\Firebase\Facades\Firebase;

class NotificationService
{
    protected $messaging;

    public function __construct()
    {
        $this->messaging = Firebase::messaging();
    }

    public function sendNotificationToTokens(
        string $title,
        string $body,
        array|string $tokens,
        array $data = [],
        NotificationType $type = NotificationType::REQUEST_PARKING_SERVICE
    ): void {
        if (is_string($tokens)) {
            $tokens = explode(',', $tokens);
        }

        if (empty($tokens)) {
            Log::info("📭 No tokens provided for notification.");
            return;
        }

        $notification = FirebaseNotification::create($title, $body);
        $message = CloudMessage::new()
            ->withNotification($notification)
            ->withData([
                'type' => $type->value,
                'extra' => json_encode($data),
            ]);

        try {
            $this->messaging->sendMulticast($message, $tokens);
            Log::info("✅ Notification sent to " . count($tokens) . " tokens.");
        } catch (\Throwable $e) {
            Log::error("❌ FCM Multicast Error: " . $e->getMessage());
        }

        // Save to database
        ModelNotification::create([
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'data' => $data,
        ]);
    }
}
