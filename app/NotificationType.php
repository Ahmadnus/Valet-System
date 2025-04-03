<?php

namespace App;
enum NotificationType: string
{
    case CHAT_MESSAGE = 'chat_message';
    case ORDER_UPDATE = 'order_update';
    case SYSTEM_ALERT = 'system_alert';
    case PROMOTION = 'promotion';

    public function label(): string
    {
        return match ($this) {
            self::CHAT_MESSAGE => 'Chat Message',
            self::ORDER_UPDATE => 'Order Update',
            self::SYSTEM_ALERT => 'System Alert',
            self::PROMOTION => 'Promotion Notification',
        };
    }

    public function successMessage(): string
    {
        return match ($this) {
            self::CHAT_MESSAGE => 'Message sent successfully.',
            self::ORDER_UPDATE => 'Order updated and notification sent.',
            self::SYSTEM_ALERT => 'System alert delivered.',
            self::PROMOTION => 'Promotion sent!',
        };
    }

    public function failureMessage(): string
    {
        return match ($this) {
            self::CHAT_MESSAGE => 'Failed to send message.',
            self::ORDER_UPDATE => 'Order update failed to notify.',
            self::SYSTEM_ALERT => 'Could not deliver system alert.',
            self::PROMOTION => 'Promotion delivery failed.',
        };
}
public function defaultTitle(): string
    {
        return match ($this) {
            self::CHAT_MESSAGE => 'New Message',
            self::ORDER_UPDATE => 'New Parking Request',
            self::SYSTEM_ALERT => 'Alert from System',
            self::PROMOTION => 'Special Promotion',
        };
    }

    public function defaultBody(array $data = []): string
    {
        return match ($this) {
            self::CHAT_MESSAGE => "You have a new message from {$data['sender_name'] ?? 'someone'}.",
            self::ORDER_UPDATE => "User {$data['user_name'] ?? 'a user'} has requested parking.",
            self::SYSTEM_ALERT => $data['message'] ?? 'A system alert has been issued.',
            self::PROMOTION => "Don't miss out on {$data['offer'] ?? 'a great deal'}!",
        };
}}
