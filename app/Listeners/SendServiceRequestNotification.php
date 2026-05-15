<?php

namespace App\Listeners;

use App\Events\ServiceRequestCreated;
use App\Mail\NewServiceRequestMail;
use App\Models\Notification;
use App\Services\Contracts\SmsServiceInterface;
use Illuminate\Support\Facades\Mail;

class SendServiceRequestNotification
{
    protected $smsService;

    public function __construct(SmsServiceInterface $smsService)
    {
        $this->smsService = $smsService;
    }

    public function handle(ServiceRequestCreated $event): void
    {
        $request = $event->request;
        $service = $request->service;
        $office = $service->office;
        $officeUser = $office->user;
        $citizen = $request->citizen;

        // Get office phone number from office record
        $officePhone = $office->phone;
        $officeEmail = $office->email;

        // Create notification record in database
        Notification::create([
            'user_id' => $officeUser->id,
            'title' => 'New Service Request',
            'message' => "New request received from {$citizen->username} for {$service->name}",
            'type' => 'service_request',
            'is_read' => false,
        ]);

        // Send email to office
        try {
            Mail::to($officeEmail)->send(
                new NewServiceRequestMail($request, $citizen->username, $service->name)
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send new service request email: ' . $e->getMessage());
        }

        // Send SMS to office
        $smsMessage = "New service request received! Request ID: {$request->id}, From: {$citizen->username}, Service: {$service->name}";
        try {
            $this->smsService->send($officePhone, $smsMessage);
        } catch (\Exception $e) {
            \Log::error('Failed to send SMS: ' . $e->getMessage());
        }
    }
}
