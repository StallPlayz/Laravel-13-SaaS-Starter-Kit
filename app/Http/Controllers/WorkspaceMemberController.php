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
    public function index(Request $request, Workspace $workspace): RedirectResponse|Response
    {
        $user = $request->user();
        $isGhostMode = $request->session()->has('ghost_workspace_id');

        if ($isGhostMode && $user->isSuperAdmin()) {
            // Super admin in ghost mode can view any workspace directory
        } else {
            // Ensure the user belongs to this workspace
            if (!$user->workspaces()->where('workspaces.id', $workspace->id)->exists()) {
                abort(403);
            }
        }

        return Inertia::render('workspaces/Directory', [
            'workspace' => [
                'id' => $workspace->id,
                'name' => $workspace->name,
                'slug' => $workspace->slug,
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
