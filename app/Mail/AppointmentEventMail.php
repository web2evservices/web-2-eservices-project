<?php

namespace App\Mail;

use App\Models\Appointments;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentEventMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Appointments $appointment,
        public string $subjectLine,
        public string $heading,
        public string $body,
        public string $recipientName = 'there',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        $this->appointment->loadMissing(['service', 'office']);

        return new Content(
            view: 'emails.appointment-event',
            with: [
                'appointment'    => $this->appointment,
                'heading'        => $this->heading,
                'body'           => $this->body,
                'recipientName'  => $this->recipientName,
                'serviceName'    => $this->appointment->service?->name ?? 'Service',
                'officeName'     => $this->appointment->office?->name ?? 'Government Office',
                'formattedDate'  => $this->appointment->date?->format('l, F j, Y') ?? '',
                'formattedTime'  => $this->appointment->formatted_time_slot ?? $this->appointment->time_slot,
            ],
        );
    }
}
