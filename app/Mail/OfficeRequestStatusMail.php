<?php

namespace App\Mail;

use App\Models\ServiceRequests;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OfficeRequestStatusMail extends Mailable
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
            subject: "Request #{$this->serviceRequest->id} Status Updated",
        );
    }

    public function content(): Content
    {
        $this->serviceRequest->loadMissing(['service.office', 'citizen']);

        return new Content(
            view: 'emails.office.request-status-updated',
            with: [
                'serviceRequest' => $this->serviceRequest,
                'oldStatus'      => $this->oldStatus,
                'newStatus'      => $this->newStatus,
            ],
        );
    }
}
