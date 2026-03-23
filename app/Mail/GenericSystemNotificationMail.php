<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GenericSystemNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $title,
        public string $body,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address('bksmknciamis@gmail.com', 'BK SMKN 1 Ciamis'),
            subject: $this->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.generic-notification',
            with: [
                'title' => $this->title,
                'body' => $this->body,
            ]
        );
    }
}
