<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ImpersonationStarted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $adminName) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Platform Support: Account Access Initiated');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.impersonation.started');
    }
}