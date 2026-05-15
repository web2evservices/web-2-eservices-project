<?php

namespace App\Listeners;

use App\Events\AppointmentReminderTriggered;
use App\Mail\AppointmentReminderMail;
use App\Models\Notification;
use App\Services\Contracts\SmsServiceInterface;
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
        Notification::create([
            'user_id' => $officeUser->id,
            'title' => 'Appointment Reminder',
            'message' => "Reminder: Appointment with {$citizenName} tomorrow at {$appointmentDate}",
            'type' => 'appointment_reminder',
            'is_read' => false,
        ]);

        // Send email to office
        try {
            Mail::to($officeEmail)->send(
                new AppointmentReminderMail($appointment, $service->name)
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send appointment reminder email: ' . $e->getMessage());
        }

        // Send SMS to office
        $smsMessage = "Reminder: Appointment with {$citizenName} tomorrow at {$appointmentDate} for {$service->name}";
        try {
            $this->smsService->send($officePhone, $smsMessage);
        } catch (\Exception $e) {
            \Log::error('Failed to send SMS: ' . $e->getMessage());
        }
    }
}
