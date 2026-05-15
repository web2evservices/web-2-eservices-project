<?php

namespace App\Mail;

use App\Models\Appointments;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Appointments $appointment,
        public string $serviceName
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Appointment Reminder - {$this->serviceName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.office.appointment-reminder',
            with: [
                'appointment' => $this->appointment,
                'serviceName' => $this->serviceName,
            ],
        );
    }
}
