<?php

namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Console\Scheduling\Schedule;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\SendAppointmentReminders::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('appointments:send-reminders')->hourly();
    }
}
