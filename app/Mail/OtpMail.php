<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $code,
        public int $expiresIn
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your Login Code');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.otp');
    }
}
