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
        $rawSearch = $request->search ?? '';
        $tokens = [];
        $generalSearch = $rawSearch;

        if (preg_match_all('/(\w+):(".*?"|\S+)/', $rawSearch, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $key = strtolower($match[1]);
                $value = trim($match[2], '"');
                $tokens[$key][] = $value;
                $generalSearch = str_replace($match[0], '', $generalSearch);
            }
        }
        $generalSearch = trim($generalSearch);

        $workspaces = Workspace::with('owner')
            ->withCount('users')
            ->when($generalSearch, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                      ->orWhereHas('owner', function($q2) use ($search) {
                          $q2->where('name', 'ilike', "%{$search}%")
                            ->orWhere('email', 'ilike', "%{$search}%");
                      });
                });
            })
            ->when(isset($tokens['owner']), function ($query) use ($tokens) {
                $query->whereHas('owner', function ($q) use ($tokens) {
                    $q->where('name', 'ilike', "%{$tokens['owner'][0]}%");
                });
            })
            ->when(isset($tokens['users']), function ($query) use ($tokens) {
                $query->has('users', '=', $tokens['users'][0]);
            })
            ->when(isset($tokens['tier']), function ($query) use ($tokens) {
                $query->whereIn('tier', $tokens['tier']);
            })
            ->when(isset($tokens['status']), function ($query) use ($tokens) {
                $status = strtolower($tokens['status'][0]);
                if ($status === 'suspended') {
                    $query->where('is_suspended', true);
                } elseif ($status === 'active') {
                    $query->where('is_suspended', false);
                }
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
