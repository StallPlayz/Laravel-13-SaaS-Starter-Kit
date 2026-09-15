<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;
use App\Mail\ImpersonationStarted;
use App\Mail\ImpersonationEnded;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $users = User::withCount('workspaces')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/Users', [
            'users' => $users,
            'filters' => $request->only(['search']),
        ]);
    }

    public function impersonate(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'pin' => ['required', 'string'],
        ]);

        if (!$user->support_pin || $user->support_pin !== $request->pin || $user->support_pin_expires_at?->isPast()) {
            return back()->with('error', 'Invalid or expired Support PIN.');
        }

        $admin = $request->user();

        Mail::to($user->email)->send(new ImpersonationStarted($admin->name));

        $request->session()->put('impersonator_id', $admin->id);
        $request->session()->put('impersonator_name', $admin->name);
        Auth::login($user);

        $user->forceFill([
            'support_pin' => null,
            'support_pin_expires_at' => null,
        ])->save();

        return redirect()->route('dashboard')->with('success', "You are now impersonating {$user->name}.");
    }

    public function leaveImpersonation(Request $request): RedirectResponse
    {
        if (!$request->session()->has('impersonator_id')) {
            return redirect()->route('dashboard');
        }

        $adminId = $request->session()->pull('impersonator_id');
        $adminName = $request->session()->pull('impersonator_name', 'A Platform Administrator');
        $owner = $request->user();

        Mail::to($owner->email)->send(new ImpersonationEnded($adminName));

        $request->session()->forget('active_workspace_id');
        $request->session()->forget('ghost_workspace_id');

        Auth::loginUsingId($adminId);

        return redirect()->route('admin.users.index')->with('success', 'Impersonation ended.');
    }
}
