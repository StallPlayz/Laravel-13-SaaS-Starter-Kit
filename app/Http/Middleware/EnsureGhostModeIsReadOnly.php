<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGhostModeIsReadOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->has('ghost_workspace_id')) {
            $readOnlyMethods = ['GET', 'HEAD', 'OPTIONS'];

            if (! in_array($request->method(), $readOnlyMethods)) {
                if (! $request->routeIs('admin.workspaces.ghost.exit')) {
                    return back()->with('error', 'Ghost Mode is active. Modifications are disabled.');
                }
            }
        }

        return $next($request);
    }
}
