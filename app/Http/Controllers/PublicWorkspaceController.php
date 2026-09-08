<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Inertia\Inertia;
use Inertia\Response;

class PublicWorkspaceController extends Controller
{
    public function show(Workspace $workspace): Response
    {
        return Inertia::render('workspaces/Public', [
            'workspace' => $workspace->only(['name', 'slug', 'tier', 'settings']),
        ]);
    }
}
