<?php

namespace App\Mail;

use App\Models\Messages;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CitizenChatReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Messages $message,
        public string $officeName
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New Reply from {$this->officeName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.citizen.chat-reply',
            with: [
                'message'    => $this->message,
                'officeName' => $this->officeName,
            ],
        );
    }
}