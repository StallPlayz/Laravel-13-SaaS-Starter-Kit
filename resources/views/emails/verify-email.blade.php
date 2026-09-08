<x-mail::message>
# Verify Your Email Address

Hello {{ $user->name }},

Thank you for registering! Please verify your email address to complete your account setup.

<x-mail::button :url="$url" color="primary">
Verify Email Address
</x-mail::button>

<x-mail::panel>
**Note:** This verification link is temporary and will safely expire in **{{ $expiresIn }} minutes**.
</x-mail::panel>

If you did not create an account, you can safely ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>