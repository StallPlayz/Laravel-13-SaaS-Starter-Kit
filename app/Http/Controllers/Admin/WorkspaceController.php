<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WorkspaceController extends Controller
{
    /**
     * Display a listing of all workspaces across the platform.
     */
    public function index(Request $request): Response
    {
        $workspaces = Workspace::with('owner')
            ->withCount('users')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhereHas('owner', function($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                      });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/Workspaces', [
            'workspaces' => $workspaces,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Suspend or unsuspend a workspace.
     */
    public function toggleSuspension(Workspace $workspace): RedirectResponse
    {
        $workspace->is_suspended = ! $workspace->is_suspended;
        $workspace->suspended_at = $workspace->is_suspended ? now() : null;
        $workspace->save();

        $status = $workspace->is_suspended ? 'suspended' : 'unsuspended';

        return back()->with('success', "Workspace has been successfully {$status}.");
    }

    /**
     * Enter Ghost Mode for a target workspace.
     */
    public function enterGhostMode(Request $request, Workspace $workspace): RedirectResponse
    {
        $request->session()->put('ghost_workspace_id', $workspace->id);
        $request->session()->put('active_workspace_id', $workspace->id);

        return redirect()->route('dashboard')->with('success', "Entered Ghost Mode for {$workspace->name}.");
    }

    /**
     * Exit Ghost Mode and return to admin context.
     */
    public function exitGhostMode(Request $request): RedirectResponse
    {
        $request->session()->forget(['ghost_workspace_id', 'active_workspace_id']);

        return redirect()->route('admin.workspaces.index')->with('success', 'Exited Ghost Mode safely.');
    }
}
