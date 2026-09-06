<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Inertia\Inertia;

class PublicWorkspaceController extends Controller
{
    public function show(Workspace $workspace)
    {
        return Inertia::render('workspaces/Public', [
            'workspace' => $workspace->only(['name', 'slug', 'tier', 'settings']),
        ]);
    }
}
