<?php

namespace App\Services;

use App\Models\Notifications;
use App\Models\User;

class NotificationService
{
    public static function send(int $userId, string $title, string $message, string $type): Notifications
    {
        return Notifications::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'is_read' => false,
        ]);
    }

    public static function exists(int $userId, string $title, string $message, string $type): bool
    {
        return Notifications::where([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
        ])->exists();
    }

    public static function sendToAdmins(string $title, string $message, string $type): void
    {
        User::where('role', 'admin')->get()->each(function (User $admin) use ($title, $message, $type) {
            if (!self::exists($admin->id, $title, $message, $type)) {
                self::send($admin->id, $title, $message, $type);
            }
        });
    }
}
