<?php

namespace App\Listeners;

use App\Events\AppointmentReminderTriggered;
use App\Mail\AppointmentReminderMail;
use App\Mail\CitizenAppointmentReminderMail;
use App\Services\Contracts\SmsServiceInterface;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class SendAppointmentReminderNotification
{
    protected $smsService;

    public function __construct(
        SmsServiceInterface $smsService,
        protected NotificationService $notifications
    ) {
        $this->smsService = $smsService;
    }

    public function handle(AppointmentReminderTriggered $event): void
    {
        $appointment = $event->appointment;
        $service = $appointment->service;
        $office = $appointment->office;
        $officeUser = $office?->user;

        if (!$office || !$officeUser) {
            Log::warning('Appointment reminder skipped: office user not found for appointment ' . $appointment->id);
            return;
        }

        $officePhone = $office->contact_info ?? null;
        $officeEmail = $officeUser->email;
        $citizenName = $appointment->citizen_name;
        $appointmentDate = $appointment->date->format('Y-m-d H:i');

        $this->notifications->notifyWithEmail(
            $officeUser->id,
            'Appointment Reminder',
            "Reminder: Appointment with {$citizenName} tomorrow at {$appointmentDate}",
            'appointment_reminder',
            new AppointmentReminderMail($appointment, $service->name),
            $officeEmail
        );

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
            if ($citizen) {
                $this->notifications->notifyWithEmail(
                    $citizen->id,
                    'Appointment Reminder',
                    "Reminder: Your appointment with {$officeName} is tomorrow at {$appointmentDate}",
                    'appointment_reminder',
                    new CitizenAppointmentReminderMail($appointment, $service->name, $officeName),
                    $citizenEmail
                );
            } else {
                $this->notifications->sendEmailIfReal(
                    $citizenEmail,
                    new CitizenAppointmentReminderMail($appointment, $service->name, $officeName)
                );
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
