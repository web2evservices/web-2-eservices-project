<?php

namespace App\Listeners;

use App\Events\ServiceRequestCreated;
use App\Events\NotificationBroadcast;
use App\Mail\NewServiceRequestMail;
use App\Models\Notification;
use App\Services\Contracts\SmsServiceInterface;
use Illuminate\Support\Facades\Log;
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
        $request    = $event->request;
        $service    = $request->service;
        $office     = $service->office;
        $officeUser = $office->user;
        $citizen    = $request->citizen;

        if (!$officeUser) {
            Log::warning("No office user found for office {$office->id}, skipping notification.");
            return;
        }

        // DB notification
        $notification = Notification::create([
            'user_id' => $officeUser->id,
            'title'   => 'New Service Request',
            'message' => "a service request was sent to you bro",
            'type'    => 'service_request',
            'is_read' => false,
        ]);
        NotificationBroadcast::dispatch($notification, $officeUser->id);

        // Email to office
        try {
            Mail::to($officeUser->email)->send(
                new NewServiceRequestMail($request, $citizen->username, $service->name)
            );
        } catch (\Exception $e) {
            Log::error('Failed to send new service request email: ' . $e->getMessage());
        }

        // SMS to office
        if ($office->phone ?? null) {
            try {
                $this->smsService->send($office->phone, "a service request was sent to you bro — Request ID: {$request->id}");
            } catch (\Exception $e) {
                Log::error('Failed to send SMS: ' . $e->getMessage());
            }
        }
    }
}