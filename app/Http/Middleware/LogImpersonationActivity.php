<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogImpersonationActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->session()->has('impersonator_id')) {
            
            $mutatingMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];
            $ignoredRoutes = ['admin.impersonation.leave', 'admin.users.impersonate'];
            
           if (in_array($request->method(), $mutatingMethods) && !in_array($request->route()?->getName(), $ignoredRoutes)) {
                
                $cleanPayload = $request->except(['password', 'password_confirmation', '_token', 'pin']);

                AuditLog::create([
                    'user_id' => $request->user()->id,
                    'impersonator_id' => $request->session()->get('impersonator_id'),
                    'method' => $request->method(),
                    'url' => $request->fullUrl(),
                    'route_name' => $request->route()?->getName(),
                    'payload' => empty($cleanPayload) ? null : $cleanPayload,
                    'ip_address' => $request->ip(),
                ]);
            }
        }

        return $response;
    }
}