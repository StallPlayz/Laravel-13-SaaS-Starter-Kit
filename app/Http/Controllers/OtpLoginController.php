<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class OtpLoginController extends Controller
{
    public function requestOtp(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $executed = RateLimiter::attempt(
            'send-otp:'.$request->ip(),
            3,
            function () use ($request) {
                $code = (string) random_int(100000, 999999);
                $expiresIn = config('auth.otp.expire', 5);

                Cache::put('otp_'.$request->email, Hash::make($code), now()->addMinutes($expiresIn));

                Mail::to($request->email)->send(new OtpMail($code, $expiresIn));
            },
            60
        );

        if (! $executed) {
            throw ValidationException::withMessages([
                'email' => 'Too many attempts. Please try again in a minute.',
            ]);
        }

        return back();
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|numeric|digits:6',
        ]);

        $key = 'verify-otp:'.$request->email.'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'code' => 'Too many attempts. Please wait 5 minutes.',
            ]);
        }

        $cachedCode = Cache::get('otp_'.$request->email);

        if (! $cachedCode || ! Hash::check($request->code, $cachedCode)) {
            RateLimiter::hit($key, 300);

            throw ValidationException::withMessages([
                'code' => 'The provided code is invalid or has expired.',
            ]);
        }

        RateLimiter::clear($key);
        Cache::forget('otp_'.$request->email);

        $user = User::where('email', $request->email)->first();
        Auth::login($user);

        return redirect()->intended(route('dashboard'));
    }
}
