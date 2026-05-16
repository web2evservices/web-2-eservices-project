<?php

namespace App\Listeners;

use App\Events\RequestStatusUpdated;
use App\Models\Notification;
use App\Services\Contracts\SmsServiceInterface;
use Illuminate\Support\Facades\Mail;

class SendRequestStatusUpdateToOffice
{
    protected $smsService;

    public function __construct(SmsServiceInterface $smsService)
    {
        $this->smsService = $smsService;
    }

    public function handle(RequestStatusUpdated $event): void
    {
        $request = $event->request;
        $oldStatus = $event->oldStatus;
        $newStatus = $event->newStatus;
        
        $service = $request->service;
        $office = $service->office;
        $officeUser = $office->user;
        $citizen = $request->citizen;

        $officePhone = $office->phone;
        $officeEmail = $office->email;

        // Create notification record for office
        Notification::create([
            'user_id' => $officeUser->id,
            'title' => 'Request Status Updated',
            'message' => "Request #{$request->id} from {$citizen->username} - Status: {$oldStatus} → {$newStatus}",
            'type' => 'status_update',
            'is_read' => false,
        ]);

        // Send SMS to office confirming status update they made
        if ($officePhone) {
            $smsMessage = "Status update confirmed! Request #{$request->id} changed from {$oldStatus} to {$newStatus}.";
            try {
                $this->smsService->send($officePhone, $smsMessage);
            } catch (\Exception $e) {
                \Log::error('Failed to send SMS: ' . $e->getMessage());
            }
        } else {
            \Log::warning('Office phone number is missing, SMS not sent for status update on request ' . $request->id);
        }
    }
}
