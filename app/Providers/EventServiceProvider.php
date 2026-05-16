<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

use App\Events\ServiceRequestCreated;
use App\Events\RequestStatusUpdated;
use App\Events\FeedbackReceived;
use App\Events\ChatMessageReceived;
use App\Events\AppointmentReminderTriggered;

use App\Listeners\SendServiceRequestNotification;
use App\Listeners\SendRequestStatusNotification;
use App\Listeners\SendFeedbackNotification;
use App\Listeners\SendChatMessageNotification;
use App\Listeners\SendAppointmentReminderNotification;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        // Task 1 — new request from citizen
        ServiceRequestCreated::class => [
            SendServiceRequestNotification::class,
        ],

        // Task 1 — status update on a request
        RequestStatusUpdated::class => [
            SendRequestStatusNotification::class,
        ],

        // Task 2 — feedback from citizen
        FeedbackReceived::class => [
            SendFeedbackNotification::class,
        ],

        // Task 2 — chat message from citizen
        ChatMessageReceived::class => [
            SendChatMessageNotification::class,
        ],

        // Task 3 — 24-hour appointment reminder
        AppointmentReminderTriggered::class => [
            SendAppointmentReminderNotification::class,
        ],
    ];

    public function boot(): void {}

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}