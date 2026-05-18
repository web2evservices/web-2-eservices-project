<?php

namespace App\Listeners;

use App\Events\RequestStatusUpdated;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;

class SendRequestStatusNotification
{
    public function __construct(protected NotificationService $notifications) {}

    public function handle(RequestStatusUpdated $event): void
    {
        $this->notifications->notifyRequestStatusChange(
            $event->request,
            $event->oldStatus,
            $event->newStatus,
            Auth::id()
        );
    }
}
