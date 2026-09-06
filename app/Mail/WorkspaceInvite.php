<?php

namespace App\Mail;

use App\Models\WorkspaceInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WorkspaceInvite extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public WorkspaceInvitation $invitation,
        public string $inviterName
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You've been invited to join {$this->invitation->workspace->name} on SyncDesk",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.workspace-invite',
            with: [
                'acceptUrl' => route('invitations.accept', ['token' => $this->invitation->token]),
            ]
        );
    }
}
