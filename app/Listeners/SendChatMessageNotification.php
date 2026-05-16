<?php

namespace App\Listeners;

use App\Events\ChatMessageReceived;
use App\Events\NotificationBroadcast;
use App\Mail\ChatMessageReceivedMail;
use App\Mail\CitizenChatReplyMail;
use App\Models\Notification;
use App\Services\Contracts\SmsServiceInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendChatMessageNotification
{
    protected $smsService;

    public function __construct(SmsServiceInterface $smsService)
    {
        $this->smsService = $smsService;
    }

    public function handle(ChatMessageReceived $event): void
    {
        $message  = $event->message;
        $sender   = $message->sender;
        $receiver = $message->receiver;

        // --- CASE 1: Citizen sends → notify the office ---
        if ($sender->role === 'citizen' && $receiver->role === 'office_user') {
            $office = $receiver->office;

            if (!$office) {
                Log::warning("No office found for office_user {$receiver->id}");
                return;
            }

            $notification = Notification::create([
                'user_id' => $receiver->id,
                'title'   => 'New Message from Citizen',
                'message' => "New message from {$sender->username}: " . substr($message->message, 0, 50) . '...',
                'type'    => 'chat_message',
                'is_read' => false,
            ]);
            NotificationBroadcast::dispatch($notification, $receiver->id);

            try {
                Mail::to($office->email)->send(
                    new ChatMessageReceivedMail($message, $sender->username)
                );
            } catch (\Exception $e) {
                Log::error('Failed to send chat message email to office: ' . $e->getMessage());
            }

            if ($office->phone ?? null) {
                try {
                    $this->smsService->send($office->phone, "New message from {$sender->username}: " . substr($message->message, 0, 40) . '...');
                } catch (\Exception $e) {
                    Log::error('Failed to send SMS to office: ' . $e->getMessage());
                }
            }
        }

        // --- CASE 2: Office replies → notify the citizen ---
        elseif ($sender->role === 'office_user' && $receiver->role === 'citizen') {
            $office     = $sender->office;
            $officeName = $office ? $office->name : 'Government Office';

            $notification = Notification::create([
                'user_id' => $receiver->id,
                'title'   => 'New Reply from Office',
                'message' => "You have a new reply from {$officeName}: " . substr($message->message, 0, 50) . '...',
                'type'    => 'chat_reply',
                'is_read' => false,
            ]);
            NotificationBroadcast::dispatch($notification, $receiver->id);

            try {
                Mail::to($receiver->email)->send(
                    new CitizenChatReplyMail($message, $officeName)
                );
            } catch (\Exception $e) {
                Log::error('Failed to send chat reply email to citizen: ' . $e->getMessage());
            }
        }
    }
}