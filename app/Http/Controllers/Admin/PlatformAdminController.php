<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Workspace;
use Inertia\Inertia;
use Inertia\Response;

class PlatformAdminController extends Controller
{
    public function dashboard(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'totalAgencies' => Workspace::count(),
                'totalUsers' => User::count(),
                'recentAgencies' => Workspace::with('owner:id,name,email')
                    ->latest()
                    ->take(5)
                    ->get(['id', 'owner_id', 'name', 'created_at']),
            ],
        ]);
    }
}
