<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;

class WorkspaceController extends Controller
{
    public function switch(Request $request)
    {
        $request->validate([
            'workspace_id' => ['required', 'exists:workspaces,id'],
        ]);

        $workspace = Workspace::findOrFail($request->workspace_id);

        if (! Gate::allows('view-workspace', $workspace)) {
            abort(403);
        }

        $request->session()->put('active_workspace_id', $workspace->id);

        return back();
    }

    public function create()
    {
        return Inertia::render('workspaces/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $ownedWorkspacesCount = $user->workspaces()->wherePivot('role', 'owner')->count();

        if ($ownedWorkspacesCount >= $user->max_workspaces) {
            return back()->withErrors(['name' => 'You have reached the maximum number of workspaces for your account tier.']);
        }

        $workspace = Workspace::create([
            'owner_id' => $user->id,
            'name' => $request->name,
            'slug' => Str::slug($request->name).'-'.uniqid(),
            'tier' => 'free',
        ]);

        $user->workspaces()->attach($workspace->id, ['role' => 'owner']);

        $request->session()->put('active_workspace_id', $workspace->id);

        return redirect()->route('dashboard');
    }

    public function settings(Workspace $workspace)
    {
        if (! Gate::allows('manage-workspace', $workspace)) {
            abort(403);
        }

        return Inertia::render('workspaces/Settings', [
            'workspace' => $workspace,
        ]);
    }

    public function update(Request $request, Workspace $workspace)
    {
        if (! Gate::allows('manage-workspace', $workspace)) {
            abort(403);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $workspace->update([
            'name' => $request->name,
        ]);

        return back()->with('success', 'Workspace updated successfully.');
    }
}
