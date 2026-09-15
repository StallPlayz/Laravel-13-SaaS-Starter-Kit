<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ImpersonationEnded extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $adminName) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Platform Support: Account Access Concluded');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.impersonation.ended');
    }
}