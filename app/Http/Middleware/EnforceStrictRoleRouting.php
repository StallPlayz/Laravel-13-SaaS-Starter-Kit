<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceStrictRoleRouting
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return $next($request);
        }

        $isSuperAdmin = $user->isSuperAdmin();
        $isAdminRoute = $request->is('admin') || $request->is('admin/*');
        $routeName = $request->route()?->getName();
        
        $isImpersonating = $request->session()->has('impersonator_id');
        $isGhostMode = $request->session()->has('ghost_workspace_id');

        if (!$isSuperAdmin) {
            if ($isAdminRoute && $routeName !== 'admin.impersonation.leave') {
                return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
            }
        }

        if ($isSuperAdmin && !$isAdminRoute && !$isImpersonating && !$isGhostMode) {
 
            $sharedRoutes = [
                'home',
                'workspace.public',
                'profile.edit',
                'profile.update',
                'profile.destroy',
                'password.edit',
                'password.update',
                'logout',
            ];

            $isSharedApi = $request->is('api/*');

            if (!in_array($routeName, $sharedRoutes) && !$isSharedApi) {
                return redirect()->route('admin.dashboard'); 
            }
        }

        return $next($request);
    }
}