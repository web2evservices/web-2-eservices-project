<?php

namespace App\Listeners;

use App\Events\ChatMessageReceived;
use App\Mail\ChatMessageReceivedMail;
use App\Models\Notification;
use App\Services\Contracts\SmsServiceInterface;
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
        $message = $event->message;
        $sender = $message->sender;
        $receiver = $message->receiver;

        // Only send notification if receiver is office user (not citizen)
        if ($receiver->role !== 'office_user') {
            return;
        }

        $office = $receiver->office;

        if (!$office) {
            return;
        }

        $officePhone = $office->phone;
        $officeEmail = $office->email;

        // Create notification record
        Notification::create([
            'user_id' => $receiver->id,
            'title' => 'New Message',
            'message' => "New message from {$sender->username}: " . substr($message->message, 0, 50) . "...",
            'type' => 'chat_message',
            'is_read' => false,
        ]);

        // Send email to office
        try {
            Mail::to($officeEmail)->send(
                new ChatMessageReceivedMail($message, $sender->username)
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send chat message email: ' . $e->getMessage());
        }

        // Send SMS to office
        $smsMessage = "New message from {$sender->username}: " . substr($message->message, 0, 40) . "...";
        try {
            $this->smsService->send($officePhone, $smsMessage);
        } catch (\Exception $e) {
            \Log::error('Failed to send SMS: ' . $e->getMessage());
        }
    }
}
