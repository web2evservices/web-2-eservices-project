<?php

namespace App\Mail;

use App\Models\Feddback;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FeedbackReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Feddback $feedback,
        public string $citizenName,
        public string $serviceName
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New Feedback Received - Service Request #{$this->feedback->service_request_id}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.office.feedback-received',
            with: [
                'feedback' => $this->feedback,
                'citizenName' => $this->citizenName,
                'serviceName' => $this->serviceName,
            ],
        );
    }
}
