<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveWorkspace
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return $next($request);
        }

        $activeWorkspaceId = $request->session()->get('active_workspace_id');

        if (! $activeWorkspaceId) {
            $workspace = $request->user()->workspaces()->first();

            if ($workspace) {
                $request->session()->put('active_workspace_id', $workspace->id);
            }
        }

        return $next($request);
    }
}
