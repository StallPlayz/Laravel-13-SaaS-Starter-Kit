<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class WorkspaceMemberController extends Controller
{
    public function index(Request $request): RedirectResponse|Response
    {
        $activeWorkspaceId = $request->session()->get('active_workspace_id');

        if (! $activeWorkspaceId) {
            return redirect()->route('dashboard')
                ->with('error', 'Please select a workspace to view its directory.');
        }

        $user = $request->user();
        $isGhostMode = $request->session()->has('ghost_workspace_id');

        if ($isGhostMode && $user->isSuperAdmin()) {
            /** @var Workspace $workspace */
            $workspace = Workspace::findOrFail($activeWorkspaceId);
        } else {
            /** @var Workspace $workspace */
            $workspace = $user->workspaces()
                ->where('workspaces.id', $activeWorkspaceId)
                ->firstOrFail();
        }

        return Inertia::render('workspaces/Directory', [
            'workspace' => [
                'id' => $workspace->id,
                'name' => $workspace->name,
            ],
            'members' => $workspace->users->map(function ($workspaceUser) {
                /** @var Pivot $pivot */
                $pivot = $workspaceUser->getAttribute('pivot');

                return [
                    'id' => $workspaceUser->id,
                    'name' => $workspaceUser->name,
                    'email' => $workspaceUser->email,
                    'role' => $pivot->getAttribute('role'),
                ];
            }),
            'pendingInvitations' => $workspace->invitations->map(function ($invite) {
                return [
                    'id' => $invite->id,
                    'email' => $invite->email,
                    'role' => $invite->role,
                    'expires_at' => Carbon::parse($invite->expires_at)->diffForHumans(),
                    'is_expired' => Carbon::parse($invite->expires_at)->isPast(),
                ];
            }),
        ]);
    }
}
