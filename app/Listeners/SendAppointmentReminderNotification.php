<?php

namespace App\Listeners;

use App\Events\AppointmentReminderTriggered;
use App\Events\NotificationBroadcast;
use App\Mail\AppointmentReminderMail;
use App\Mail\CitizenAppointmentReminderMail;
use App\Models\Notification;
use App\Services\Contracts\SmsServiceInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAppointmentReminderNotification
{
    protected $smsService;

    public function __construct(SmsServiceInterface $smsService)
    {
        $this->smsService = $smsService;
    }

    public function handle(AppointmentReminderTriggered $event): void
    {
        $appointment = $event->appointment;
        $service = $appointment->service;
        $office = $appointment->office;
        $officeUser = $office->user;

        $officePhone = $office->phone;
        $officeEmail = $office->email;
        $citizenName = $appointment->citizen_name;
        $appointmentDate = $appointment->date->format('Y-m-d H:i');

        // Create notification record
        $officeNotification = Notification::create([
            'user_id' => $officeUser->id,
            'title' => 'Appointment Reminder',
            'message' => "Reminder: Appointment with {$citizenName} tomorrow at {$appointmentDate}",
            'type' => 'appointment_reminder',
            'is_read' => false,
        ]);
        NotificationBroadcast::dispatch($officeNotification, $officeUser->id);

        // Send email to office
        try {
            Mail::to($officeEmail)->send(
                new AppointmentReminderMail($appointment, $service->name)
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send appointment reminder email: ' . $e->getMessage());
        }

        // Send SMS to office
        if ($officePhone) {
            $smsMessage = "Reminder: Appointment with {$citizenName} tomorrow at {$appointmentDate} for {$service->name}";
            try {
                $this->smsService->send($officePhone, $smsMessage);
            } catch (\Exception $e) {
                \Log::error('Failed to send SMS: ' . $e->getMessage());
            }
        } else {
            \Log::warning('Office phone number is missing, SMS not sent for appointment reminder');
        }

        // --- Notify the CITIZEN (DB + Email + SMS) ---
        $citizen = $appointment->citizen;
        $citizenEmail = $appointment->citizen_email;
        $citizenPhone = $appointment->citizen_phone;
        $officeName = $office->name ?? 'Government Office';

        if ($citizen || $citizenEmail) {
            // Create notification record for citizen
            if ($citizen) {
                $citizenNotification = Notification::create([
                    'user_id' => $citizen->id,
                    'title' => 'Appointment Reminder',
                    'message' => "Reminder: Your appointment with {$officeName} is tomorrow at {$appointmentDate}",
                    'type' => 'appointment_reminder',
                    'is_read' => false,
                ]);
                NotificationBroadcast::dispatch($citizenNotification, $citizen->id);
            }

            // Send email to citizen
            try {
                Mail::to($citizenEmail)->send(
                    new CitizenAppointmentReminderMail($appointment, $service->name, $officeName)
                );
            } catch (\Exception $e) {
                Log::error('Failed to send appointment reminder email to citizen: ' . $e->getMessage());
            }

            // Send SMS to citizen
            if ($citizenPhone) {
                $citizenSmsMessage = "Reminder: Your appointment with {$officeName} is tomorrow at {$appointmentDate} for {$service->name}";
                try {
                    $this->smsService->send($citizenPhone, $citizenSmsMessage);
                } catch (\Exception $e) {
                    Log::error('Failed to send SMS to citizen: ' . $e->getMessage());
                }
            }
        }
    }
}
