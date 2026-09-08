<x-mail::message>
# Workspace Invitation

Hello,

**{{ $inviterName }}** has invited you to join the **{{ $invitation->workspace->name }}** workspace as a **{{ ucfirst($invitation->role) }}**.

Click the button below to accept the invitation and set up your account.

<x-mail::button :url="$acceptUrl" color="primary">
Accept Invitation
</x-mail::button>

<x-mail::panel>
**Note:** This invitation link is temporary and will safely expire in **{{ $expiresIn }} hours**.
</x-mail::panel>

If you did not expect this invitation, you can safely ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>