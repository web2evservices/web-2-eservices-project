<?php

namespace App\Mail;

use App\Models\Messages;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ChatMessageReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Messages $message,
        public string $senderName
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New Message from {$this->senderName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.office.message-received',
            with: [
                'message' => $this->message,
                'senderName' => $this->senderName,
            ],
        );
    }
}
