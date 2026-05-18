<?php

namespace App\Mail;

use App\Models\ServiceRequests;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewServiceRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ServiceRequests $request,
        public string $citizenName,
        public string $serviceName
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New Service Request - Request #{$this->request->id}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.office.new-request',
            with: [
                'request' => $this->request,
                'citizenName' => $this->citizenName,
                'serviceName' => $this->serviceName,
            ],
        );
    }
}
