<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\ServiceRequestCreated;
use App\Events\FeedbackReceived;
use App\Events\ChatMessageReceived;
use App\Events\AppointmentReminderTriggered;
use App\Events\RequestStatusUpdated;
use App\Listeners\SendServiceRequestNotification;
use App\Listeners\SendFeedbackNotification;
use App\Listeners\SendChatMessageNotification;
use App\Listeners\SendAppointmentReminderNotification;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        ServiceRequestCreated::class => [
            SendServiceRequestNotification::class,
        ],
        FeedbackReceived::class => [
            SendFeedbackNotification::class,
        ],
        ChatMessageReceived::class => [
            SendChatMessageNotification::class,
        ],
        AppointmentReminderTriggered::class => [
            SendAppointmentReminderNotification::class,
        ],
        RequestStatusUpdated::class => [
            // Additional listeners can be added here
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
