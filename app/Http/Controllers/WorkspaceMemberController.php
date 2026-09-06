<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class WorkspaceMemberController extends Controller
{
    public function index(Request $request)
    {
        $activeWorkspaceId = $request->session()->get('active_workspace_id');

        if (! $activeWorkspaceId) {
            return redirect()->route('dashboard')
                ->with('error', 'Please select a workspace to view its directory.');
        }

        $workspace = $request->user()->workspaces()
            ->where('workspaces.id', $activeWorkspaceId)
            ->firstOrFail();

        return Inertia::render('workspaces/Directory', [
            'workspace' => [
                'id' => $workspace->id,
                'name' => $workspace->name,
            ],
            'members' => $workspace->users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->pivot->role,
                ];
            }),
            'pendingInvitations' => $workspace->invitations->map(function ($invite) {
                return [
                    'id' => $invite->id,
                    'email' => $invite->email,
                    'role' => $invite->role,
                    'expires_at' => $invite->expires_at->diffForHumans(),
                    'is_expired' => $invite->expires_at->isPast(),
                ];
            }),
        ]);
    }
}
