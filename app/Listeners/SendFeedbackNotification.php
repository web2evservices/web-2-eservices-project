<?php

namespace App\Listeners;

use App\Events\FeedbackReceived;
use App\Mail\FeedbackReceivedMail;
use App\Models\Notification;
use App\Services\Contracts\SmsServiceInterface;
use Illuminate\Support\Facades\Mail;

class SendFeedbackNotification
{
    protected $smsService;

    public function __construct(SmsServiceInterface $smsService)
    {
        $this->smsService = $smsService;
    }

    public function handle(FeedbackReceived $event): void
    {
        $feedback = $event->feedback;
        $citizen = $feedback->citizen;
        $request = $feedback->serviceRequest;
        $service = $request->service;
        $office = $service->office;
        $officeUser = $office->user;

        $officePhone = $office->phone;
        $officeEmail = $office->email;

        // Create notification record
        Notification::create([
            'user_id' => $officeUser->id,
            'title' => 'New Feedback Received',
            'message' => "Feedback received from {$citizen->username}: Rating {$feedback->rating}/5",
            'type' => 'feedback',
            'is_read' => false,
        ]);

        // Send email to office
        try {
            Mail::to($officeEmail)->send(
                new FeedbackReceivedMail($feedback, $citizen->username, $service->name)
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send feedback email: ' . $e->getMessage());
        }

        // Send SMS to office
        $smsMessage = "New feedback received from {$citizen->username}! Rating: {$feedback->rating}/5. Request ID: {$request->id}";
        try {
            $this->smsService->send($officePhone, $smsMessage);
        } catch (\Exception $e) {
            \Log::error('Failed to send SMS: ' . $e->getMessage());
        }
    }
}
