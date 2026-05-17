<?php

namespace App\Services;

use App\Events\NotificationBroadcast;
use App\Mail\AppointmentEventMail;
use App\Mail\CitizenRequestStatusMail;
use App\Mail\NotificationMail;
use App\Mail\OfficeRequestStatusMail;
use App\Models\Appointments;
use App\Models\Notification;
use App\Models\Office;
use App\Models\ServiceRequests;
use App\Models\User;
use App\Support\RealEmail;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function notify(int $userId, string $title, string $message, string $type = 'notification'): ?Notification
    {
        try {
            $notification = Notification::create([
                'user_id' => $userId,
                'title'   => $title,
                'message' => $message,
                'type'    => $type,
                'is_read' => false,
            ]);

            $this->broadcast($notification, $userId);

            return $notification;
        } catch (\Throwable $e) {
            Log::error('Failed to create notification: ' . $e->getMessage());

            return null;
        }
    }

    public function notifyWithEmail(
        int $userId,
        string $title,
        string $message,
        string $type = 'notification',
        ?Mailable $mailable = null,
        ?string $email = null
    ): ?Notification {
        $notification = $this->notify($userId, $title, $message, $type);

        $address = $email ?? User::find($userId)?->email;
        if ($notification && $mailable === null) {
            $mailable = new NotificationMail($notification);
        }

        $this->sendEmailIfReal($address, $mailable);

        return $notification;
    }

    public function notifyRequestStatusChange(
        ServiceRequests $serviceRequest,
        string $oldStatus,
        string $newStatus,
        ?int $actingOfficeUserId = null
    ): void {
        $serviceRequest->loadMissing(['service.office.user', 'citizen']);

        $citizen = $serviceRequest->citizen;
        if ($citizen) {
            $citizenEmail = $citizen->email ?? User::find($citizen->id)?->email;

            $this->notifyWithEmail(
                $citizen->id,
                'Your Request Status Was Updated',
                "Your request #{$serviceRequest->id} status changed from '{$oldStatus}' to '{$newStatus}'.",
                'request_status',
                new CitizenRequestStatusMail($serviceRequest, $oldStatus, $newStatus),
                $citizenEmail
            );
        }

        $governmentOffice = $serviceRequest->service?->office;
        $officeUserId     = $governmentOffice?->user_id ?? $actingOfficeUserId;

        if (! $officeUserId) {
            return;
        }

        $officeUser    = User::find($officeUserId);
        $officeProfile = Office::where('user_id', $officeUserId)->first();
        $officeEmail   = $officeUser?->email ?? $officeProfile?->email;

        $this->notifyWithEmail(
            $officeUserId,
            'Request Status Changed',
            "Request #{$serviceRequest->id} changed from '{$oldStatus}' to '{$newStatus}'.",
            'request_status',
            new OfficeRequestStatusMail($serviceRequest, $oldStatus, $newStatus),
            $officeEmail
        );
    }

    public function sendEmailIfReal(?string $email, ?Mailable $mailable): bool
    {
        if ($mailable === null || $email === null || trim($email) === '') {
            return false;
        }

        $maySend = RealEmail::isReal($email) || config('mail.default') === 'log';

        if (! $maySend) {
            return false;
        }

        try {
            Mail::to($email)->send($mailable);

            return true;
        } catch (\Throwable $e) {
            Log::error("Failed to send email to {$email}: " . $e->getMessage());

            return false;
        }
    }

    public function broadcast(Notification $notification, int $userId): void
    {
        try {
            NotificationBroadcast::dispatch($notification, $userId);
        } catch (\Throwable $e) {
            Log::warning('Notification broadcast failed: ' . $e->getMessage());
        }
    }

    public function notifyCitizenForAppointment(
        Appointments $appointment,
        string $title,
        string $message,
        string $type = 'appointment_reminder',
        ?string $emailSubject = null,
        ?string $emailBody = null
    ): void {
        if ($appointment->citizen_id) {
            $this->notifyWithEmail(
                $appointment->citizen_id,
                $title,
                $message,
                $type,
                $this->appointmentMail($appointment, $emailSubject ?? $title, $emailBody ?? $message, $appointment->citizen_name)
            );
        } else {
            $this->sendAppointmentEmailToAddress(
                $appointment->citizen_email,
                $appointment,
                $emailSubject ?? $title,
                $emailBody ?? $message,
                $appointment->citizen_name
            );
        }
    }

    public function notifyOfficeForAppointment(
        Appointments $appointment,
        string $title,
        string $message,
        string $type = 'appointment_reminder',
        ?string $emailSubject = null,
        ?string $emailBody = null
    ): void {
        $appointment->loadMissing('office.user');
        $officeUser = $appointment->office?->user;

        if (! $officeUser) {
            return;
        }

        $this->notifyWithEmail(
            $officeUser->id,
            $title,
            $message,
            $type,
            $this->appointmentMail($appointment, $emailSubject ?? $title, $emailBody ?? $message, $officeUser->username ?? 'Office Staff'),
            $officeUser->email
        );
    }

    public function notifyBothForAppointment(
        Appointments $appointment,
        string $citizenTitle,
        string $citizenMessage,
        string $officeTitle,
        string $officeMessage,
        string $type = 'appointment_reminder',
        ?string $emailSubject = null
    ): void {
        $this->notifyCitizenForAppointment(
            $appointment,
            $citizenTitle,
            $citizenMessage,
            $type,
            $emailSubject ?? $citizenTitle,
            $citizenMessage
        );

        $this->notifyOfficeForAppointment(
            $appointment,
            $officeTitle,
            $officeMessage,
            $type,
            $emailSubject ?? $officeTitle,
            $officeMessage
        );
    }

    private function sendAppointmentEmailToAddress(
        ?string $email,
        Appointments $appointment,
        string $subject,
        string $body,
        string $recipientName
    ): void {
        $this->sendEmailIfReal(
            $email,
            $this->appointmentMail($appointment, $subject, $body, $recipientName)
        );
    }

    private function appointmentMail(
        Appointments $appointment,
        string $subject,
        string $body,
        string $recipientName
    ): AppointmentEventMail {
        return new AppointmentEventMail($appointment, $subject, $subject, $body, $recipientName);
    }
}
