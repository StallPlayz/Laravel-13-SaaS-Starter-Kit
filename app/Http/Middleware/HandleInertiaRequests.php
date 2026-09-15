<?php

namespace App\Http\Middleware;

use App\Models\Workspace;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();
        $activeWorkspace = null;
        $currentRole = null;
        $availableWorkspaces = collect([]);
        $isGhostMode = $request->session()->has('ghost_workspace_id');
        $isImpersonating = $request->session()->has('impersonator_id');

        if ($user) {
            if ($isGhostMode) {
                $activeWorkspaceId = $request->session()->get('ghost_workspace_id');
                $activeWorkspace = Workspace::find($activeWorkspaceId);
                $currentRole = 'owner';
                $availableWorkspaces = $activeWorkspace ? collect([$activeWorkspace]) : collect([]);
            } else {
                $activeWorkspaceId = $request->session()->get('active_workspace_id');
                
                $availableWorkspaces = $user->workspaces()->get();

                if ($activeWorkspaceId) {
                    $activeWorkspace = $availableWorkspaces->firstWhere('id', $activeWorkspaceId);
                    
                    if ($activeWorkspace && $activeWorkspace->pivot) {
                        $currentRole = $activeWorkspace->pivot->role;
                    }
                }
            }
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
                'activeWorkspace' => $activeWorkspace,
                'currentRole' => $currentRole,
                'availableWorkspaces' => $availableWorkspaces,
                'isGhostMode' => $isGhostMode,
                'isImpersonating' => $isImpersonating,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}