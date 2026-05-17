<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Notifications;
use App\Services\NotificationService;

class Messages extends Model
{
    protected static function booted()
    {
        static::created(function (Messages $message) {
            Notifications::create([
                'user_id' => $message->receiver_id,
                'title' => 'New message received',
                'message' => sprintf(
                    'You have a new chat message from %s regarding request #%s.',
                    $message->sender?->username ?? 'a user',
                    $message->service_request_id ?? 'N/A'
                ),
                'type' => 'chat_message',
            ]);

            NotificationService::sendToAdmins(
                'New chat activity',
                sprintf(
                    'A new chat message was sent from %s regarding request #%s.',
                    $message->sender?->username ?? 'a user',
                    $message->service_request_id ?? 'N/A'
                ),
                'admin_activity'
            );
        });
    }

    public function sender()
    {
        return $this->belongsTo(Users::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(Users::class, 'receiver_id');
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequests::class, 'service_request_id');
    }
}
