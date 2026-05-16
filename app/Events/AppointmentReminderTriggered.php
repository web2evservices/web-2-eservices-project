<?php

namespace App\Events;

use App\Models\Appointments;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AppointmentReminderTriggered
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Appointments $appointment
    ) {}
}
