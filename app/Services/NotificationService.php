<?php

namespace App\services;

class NotificationService
{
    /**
     * Create a new class instance.
     */


    protected $messaging;

    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(config('firebase.credentials'));
        $this->messaging = $factory->createMessaging();
    }

    public function sendNotification($title, $body, $token, $data = [])
    {
        $notification = Notification::create($title, $body);

        $message = CloudMessage::withTarget('token', $token)
            ->withNotification($notification)
            ->withData($data);

        try {
            $this->messaging->send($message);
            info('Notification sent successfully');
        } catch (\Throwable $th) {
            info('Failed to send notification: ' . $th->getMessage());
        }
    }
}
