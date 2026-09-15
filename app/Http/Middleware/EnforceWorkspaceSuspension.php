<?php

namespace App\Http\Middleware;

use App\Models\Workspace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceWorkspaceSuspension
{
    public function handle(Request $request, Closure $next): Response
    {
        $workspaceId = $request->session()->get('active_workspace_id');

        if ($workspaceId) {
            /** @var Workspace|null $workspace */
            $workspace = Workspace::find($workspaceId);

            if ($workspace && $workspace->is_suspended && ! $request->session()->has('ghost_workspace_id')) {

                $safeMethods = ['GET', 'HEAD', 'OPTIONS'];
                $allowedRoutes = ['workspaces.switch', 'logout', 'workspaces.store', 'admin.impersonation.leave', 'user.support-pin.store'];

                if (! in_array($request->method(), $safeMethods) && ! in_array($request->route()?->getName(), $allowedRoutes)) {
                    return back()->with('error', 'This workspace is suspended. All actions are disabled.');
                }
            }
        }

        return $next($request);
    }
}
