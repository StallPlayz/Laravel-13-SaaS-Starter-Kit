<?php

namespace App\Http\Responses;

use App\Models\WorkspaceInvitation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): Response
    {
        $user = $request->user();

        if ($request->session()->has('pending_invitation_token')) {
            $token = $request->session()->get('pending_invitation_token');
            $invitation = WorkspaceInvitation::with('workspace')->where('token', $token)->first();

            if ($invitation && ! Carbon::parse($invitation->expires_at)->isPast()) {

                if ($invitation->email === $user->email) {
                    $request->session()->forget('pending_invitation_token');

                    $invitation->workspace->users()->syncWithoutDetaching([
                        $user->id => ['role' => $invitation->role],
                    ]);

                    $workspaceName = $invitation->workspace->name;
                    $invitation->delete();

                    return redirect()->intended('/dashboard')
                        ->with('success', "You have successfully joined {$workspaceName}.");
                } else {
                    Auth::logout();

                    return redirect()->route('login')->withErrors([
                        'email' => "You must log in with {$invitation->email} to accept this workspace invitation.",
                    ]);
                }
            }
        }

        if ($user && $user->isSuperAdmin()) {
            return redirect()->intended('/admin');
        }

        return redirect()->intended('/dashboard');
    }
}
