<x-mail::message>
# You have been invited!

**{{ $inviterName }}** has invited you to join the **{{ $invitation->workspace->name }}** workspace as a {{ ucfirst($invitation->role) }}.

Click the button below to accept the invitation and set up your account.

<x-mail::button :url="$acceptUrl">
Accept Invitation
</x-mail::button>

*This link will expire in 72 hours.*

If you did not expect this invitation, you can safely ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>