<?php

namespace App\Http\Controllers;

use App\Mail\WorkspaceInvite;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class WorkspaceInvitationController extends Controller
{
    public function store(Request $request, Workspace $workspace)
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

        $invitation = WorkspaceInvitation::updateOrCreate(
            [
                'workspace_id' => $workspace->id,
                'email' => $validated['email'],
            ],
            [
                'role' => $validated['role'],
                'token' => $token,
                'expires_at' => now()->addHours(72),
            ]
        );

        Mail::to($validated['email'])->send(
            new WorkspaceInvite($invitation, $request->user()->name)
        );

        return back()->with('success', 'Invitation sent successfully!');
    }

    public function accept(Request $request, $token)
    {
        $invitation = WorkspaceInvitation::with('workspace')->where('token', $token)->firstOrFail();

        if ($invitation->expires_at->isPast()) {
            $invitation->delete();
            abort(403, 'This invitation has expired. Please request a new one.');
        }

        if (Auth::check()) {
            $user = Auth::user();

            if ($user->email !== $invitation->email) {
                Auth::logout();

                session(['pending_invitation_token' => $token]);

                return redirect()->route('login')->withErrors([
                    'email' => "This invitation is for {$invitation->email}. Please log in to that specific account to accept it.",
                ]);
            }

            $invitation->workspace->users()->syncWithoutDetaching([
                $user->id => ['role' => $invitation->role],
            ]);

            $invitation->delete();

            return redirect()->intended('/dashboard')->with('success', "You have successfully joined {$invitation->workspace->name}.");
        }

        if (User::where('email', $invitation->email)->exists()) {
            session(['pending_invitation_token' => $token]);

            return redirect()->route('login')->with('status', 'Please log in to accept your workspace invitation.');
        }

        return inertia('auth/RegisterInvite', [
            'invitation' => $invitation,
            'token' => $token,
        ]);
    }

    public function register(Request $request, $token)
    {
        $invitation = WorkspaceInvitation::where('token', $token)->firstOrFail();

        if ($invitation->expires_at->isPast()) {
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

        $invitation->workspace->users()->attach($user->id, ['role' => $invitation->role]);

        $invitation->delete();

        Auth::login($user);

        return redirect()->intended('/dashboard');
    }
}
