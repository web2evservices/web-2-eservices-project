<?php

namespace App\Console\Commands;

use App\Models\Appointments;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders';

    protected $description = 'Send appointment reminders 24 hours before the scheduled appointment time.';

    public function handle(): int
    {
        $now = Carbon::now();
        $windowStart = $now->copy()->addDay()->subMinutes(30);
        $windowEnd = $now->copy()->addDay()->addMinutes(30);

        $appointments = Appointments::with(['office', 'service'])
            ->whereIn('status', ['Scheduled', 'Confirmed'])
            ->get()
            ->filter(function (Appointments $appointment) use ($windowStart, $windowEnd) {
                if (!$appointment->citizen_id) {
                    return false;
                }

                $appointmentAt = Carbon::parse("{$appointment->date} {$appointment->time_slot}");
                return $appointmentAt->between($windowStart, $windowEnd);
            });

        $sent = 0;

        foreach ($appointments as $appointment) {
            $title = 'Appointment reminder';
            $message = sprintf(
                'Reminder: your appointment for %s is scheduled for %s at %s.',
                $appointment->service?->name ?? 'your service',
                $appointment->date,
                $appointment->time_slot
            );

            if (!NotificationService::exists($appointment->citizen_id, $title, $message, 'appointment_reminder')) {
                NotificationService::send(
                    $appointment->citizen_id,
                    $title,
                    $message,
                    'appointment_reminder'
                );
                $sent++;
            }

            if ($officeUserId = $appointment->office?->user_id) {
                $officeMessage = sprintf(
                    'Reminder: appointment for %s is scheduled for %s at %s.',
                    $appointment->service?->name ?? 'the service',
                    $appointment->date,
                    $appointment->time_slot
                );

                if (!NotificationService::exists($officeUserId, $title, $officeMessage, 'appointment_reminder')) {
                    NotificationService::send(
                        $officeUserId,
                        $title,
                        $officeMessage,
                        'appointment_reminder'
                    );
                }
            }
        }

        $this->info("Appointment reminder notifications checked: {$appointments->count()}, sent: {$sent}.");

        return Command::SUCCESS;
    }
}
