<?php

namespace App\Console\Commands;

use App\Events\AppointmentReminderTriggered;
use App\Models\Appointments;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointment:send-reminders';
    protected $description = 'Send appointment reminders 24 hours before scheduled appointments';

    public function handle(): int
    {
        try {
            // Get appointments scheduled for exactly 24 hours from now
            $now = Carbon::now();
            $tomorrowStart = $now->copy()->addDay()->startOfDay();
            $tomorrowEnd = $now->copy()->addDay()->endOfDay();

            // Fetch appointments scheduled for tomorrow
            $appointments = Appointments::whereBetween('date', [$tomorrowStart, $tomorrowEnd])
                ->where('status', 'Scheduled')
                ->get();

            if ($appointments->isEmpty()) {
                $this->info('No appointments found for reminder.');
                return 0;
            }

            $count = 0;
            foreach ($appointments as $appointment) {
                try {
                    AppointmentReminderTriggered::dispatch($appointment);
                    $count++;
                    $this->info("Reminder sent for appointment ID: {$appointment->id}");
                } catch (\Exception $e) {
                    $this->error("Failed to send reminder for appointment ID: {$appointment->id} - {$e->getMessage()}");
                }
            }

            $this->info("Successfully sent {$count} appointment reminders.");
            return 0;
        } catch (\Exception $e) {
            $this->error('Error sending appointment reminders: ' . $e->getMessage());
            return 1;
        }
    }
}
