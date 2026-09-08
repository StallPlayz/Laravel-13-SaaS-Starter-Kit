<?php

namespace App\Http\Controllers;

use App\Mail\WorkspaceInvite;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Str;
use Inertia\Response;

class WorkspaceInvitationController extends Controller
{
    public function store(Request $request, Workspace $workspace): RedirectResponse
    {
        // Gate::authorize('invite', $workspace);
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'in:admin,member,client'],
        ]);

        if ($workspace->users()->where('email', $validated['email'])->exists()) {
            return back()->withErrors(['email' => 'This user is already a member of the workspace.']);
        }

        $token = Str::random(64);
        $expiresIn = config('auth.invitation.expire', 72);

        $invitation = WorkspaceInvitation::updateOrCreate(
            [
                'workspace_id' => $workspace->id,
                'email' => $validated['email'],
            ],
            [
                'role' => $validated['role'],
                'token' => $token,
                'expires_at' => now()->addHours($expiresIn),
            ]
        );

        Mail::to($validated['email'])->send(
            new WorkspaceInvite($invitation, $request->user()->name, $expiresIn)
        );

        return back()->with('success', 'Invitation sent successfully!');
    }

    public function accept(Request $request, string $token): RedirectResponse|Response
    {
        $invitation = WorkspaceInvitation::with('workspace')->where('token', $token)->firstOrFail();

        /** @var Workspace $workspace */
        $workspace = $invitation->workspace;

        if (Carbon::parse($invitation->expires_at)->isPast()) {
            $invitation->delete();
            abort(403, 'This invitation has expired. Please request a new one.');
        }

        $userExists = User::where('email', $invitation->email)->exists();



        if (Auth::check()) {
            $user = Auth::user();

            if ($user->email === $invitation->email) {
                $invitation->workspace->users()->syncWithoutDetaching([
                    $user->id => ['role' => $invitation->role],
                ]);

                $invitation->delete();

                return redirect()->intended('/dashboard')->with('success', "You have successfully joined {$workspace->name}.");
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        session(['pending_invitation_token' => $token]);

        if ($userExists) {
            return inertia('auth/LoginInvite', [
                'invitation' => $invitation,
                'token' => $token,
            ]);
        }

        return inertia('auth/RegisterInvite', [
            'invitation' => $invitation,
            'token' => $token,
        ]);
    }

    public function register(Request $request, string $token): RedirectResponse
    {
        $invitation = WorkspaceInvitation::where('token', $token)->firstOrFail();

        /** @var Workspace $workspace */
        $workspace = $invitation->workspace;

        if (Carbon::parse($invitation->expires_at)->isPast()) {
            $invitation->delete();
            abort(403, 'This invitation has expired.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:50'],
            'country' => ['required', 'string', 'max:255'],
            'province' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $invitation->email,
            'phone_number' => $validated['phone_number'],
            'country' => $validated['country'],
            'province' => $validated['province'],
            'city' => $validated['city'],
            'district' => $validated['district'],
            'address' => $validated['address'],
            'password' => Hash::make($validated['password']),
            'terms' => $validated['terms'],
        ]);

        event(new Registered($user));

        $workspace->users()->attach($user->id, ['role' => $invitation->role]);

        $invitation->delete();

        Auth::login($user);

        return redirect()->intended('/dashboard');
    }
}
