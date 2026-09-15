<?php

use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

test('otp can be requested for valid email', function () {
    Mail::fake();
    $user = User::factory()->create();

    $response = $this->post('/login/otp/request', ['email' => $user->email]);

    $response->assertRedirect();
    Mail::assertSent(OtpMail::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });

    expect(Cache::has('otp_'.$user->email))->toBeTrue();
});

test('otp cannot be requested for non-existent email', function () {
    $response = $this->post('/login/otp/request', ['email' => 'fake@example.com']);
    $response->assertSessionHasErrors('email');
});

test('rate limit applies to otp requests', function () {
    $user = User::factory()->create();

    for ($i = 0; $i < 3; $i++) {
        $this->post('/login/otp/request', ['email' => $user->email]);
    }

    $response = $this->post('/login/otp/request', ['email' => $user->email]);
    $response->assertSessionHasErrors('email');
});
