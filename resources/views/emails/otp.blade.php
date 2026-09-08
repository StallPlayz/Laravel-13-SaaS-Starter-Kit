<x-mail::message>
# Your Login Verification Code

You requested a single-use code to log in to **{{ config('app.name') }}**. 

Use the code below to complete your login process:

<x-mail::panel>
# {{ $code }}
</x-mail::panel>

**Note:** This code is temporary and will expire in **{{ $expiresIn }} minutes**

If you did not request this verification code, please ignore this email or secure your account if you have concerns.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>