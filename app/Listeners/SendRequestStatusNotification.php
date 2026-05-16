<?php

namespace App\Listeners;

use App\Events\RequestStatusUpdated;
use App\Events\NotificationBroadcast;
use App\Mail\CitizenRequestStatusMail;
use App\Models\Notification;
use App\Services\Contracts\SmsServiceInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendRequestStatusNotification
{
    protected $smsService;

    public function __construct(SmsServiceInterface $smsService)
    {
        $this->smsService = $smsService;
    }

    public function handle(RequestStatusUpdated $event): void
    {
        $serviceRequest = $event->request;
        $oldStatus      = $event->oldStatus;
        $newStatus      = $event->newStatus;

        try {
            $service = $serviceRequest->service;
            if (!$service) return;

            $office  = $service->office;
            $citizen = $serviceRequest->citizen;

            // --- Notify the OFFICE (DB + SMS) ---
            if ($office && $office->user_id) {
                $notification = Notification::create([
                    'user_id' => $office->user_id,
                    'title'   => 'Request Status Changed',
                    'message' => "Request #{$serviceRequest->id} changed from '{$oldStatus}' to '{$newStatus}'.",
                    'type'    => 'request_status',
                    'is_read' => false,
                ]);
                NotificationBroadcast::dispatch($notification, $office->user_id);

                if ($office->phone ?? null) {
                    try {
                        $this->smsService->send($office->phone, "Status Update: Request #{$serviceRequest->id} — {$oldStatus} → {$newStatus}");
                    } catch (\Exception $e) {
                        Log::error('Failed to send SMS to office: ' . $e->getMessage());
                    }
                }
            }

            // --- Notify the CITIZEN (DB + Email) ---
            if ($citizen) {
                $citizenNotification = Notification::create([
                    'user_id' => $citizen->id,
                    'title'   => 'Your Request Status Was Updated',
                    'message' => "Your request #{$serviceRequest->id} status changed from '{$oldStatus}' to '{$newStatus}'.",
                    'type'    => 'request_status',
                    'is_read' => false,
                ]);
                NotificationBroadcast::dispatch($citizenNotification, $citizen->id);

                try {
                    Mail::to($citizen->email)->send(
                        new CitizenRequestStatusMail($serviceRequest, $oldStatus, $newStatus)
                    );
                } catch (\Exception $e) {
                    Log::error('Failed to send status update email to citizen: ' . $e->getMessage());
                }
            }

        } catch (\Exception $e) {
            Log::error('SendRequestStatusNotification failed: ' . $e->getMessage());
        }
    }
}