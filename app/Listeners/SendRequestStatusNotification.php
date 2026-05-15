<?php

namespace App\Listeners;

use App\Events\RequestStatusUpdated;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class SendRequestStatusNotification
{
    public function handle(RequestStatusUpdated $event): void
    {
        $serviceRequest = $event->request;
        $oldStatus      = $event->oldStatus;
        $newStatus      = $event->newStatus;

        try {
            $service = $serviceRequest->service;
            if (! $service) return;

            $office = $service->office;
            if (! $office || ! $office->user_id) return;

            Notification::create([
                'user_id' => $office->user_id,
                'title'   => 'Request Status Changed',
                'message' => "Request #{$serviceRequest->id} changed from '{$oldStatus}' to '{$newStatus}'.",
                'type'    => 'request_status',
                'is_read' => false,
            ]);
        } catch (\Exception $e) {
            Log::error('SendRequestStatusNotification failed: ' . $e->getMessage());
        }
    }
}