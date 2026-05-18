<?php

namespace App\Mail;

use App\Models\ServiceRequests;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CitizenRequestStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ServiceRequests $serviceRequest,
        public string $oldStatus,
        public string $newStatus
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your Request #{$this->serviceRequest->id} Status Has Been Updated",
        );
    }

    public function content(): Content
    {
        $this->serviceRequest->loadMissing(['service.office', 'citizen']);

        return new Content(
            view: 'emails.request-status-update',
            with: [
                'serviceRequest' => $this->serviceRequest,
                'request'        => $this->serviceRequest,
                'oldStatus'      => $this->oldStatus,
                'newStatus'      => $this->newStatus,
            ],
        );
    }
}