<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(Workspace $workspace): Response
    {
        if (! Gate::allows('view-workspace', $workspace)) {
            abort(403);
        }

        $projects = $workspace->projects()->latest()->get();

        return Inertia::render('projects/Index', [
            'workspace' => $workspace,
            'projects' => $projects,
        ]);
    }

    public function create(Workspace $workspace): Response
    {
        if (! Gate::allows('manage-workspace', $workspace)) {
            abort(403);
        }

        return Inertia::render('projects/Create', [
            'workspace' => $workspace,
        ]);
    }

    public function store(Request $request, Workspace $workspace): RedirectResponse
    {
        if (! Gate::allows('manage-workspace', $workspace)) {
            abort(403);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'alpha_dash', 'max:255', 'unique:projects,slug,NULL,id,workspace_id,' . $workspace->id],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $project = $workspace->projects()->create([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'status' => 'active',
        ]);

        return redirect()->route('projects.show', ['workspace' => $workspace->slug, 'project' => $project->slug]);
    }

    public function show(Workspace $workspace, Project $project): Response
    {
        if (! Gate::allows('view-workspace', $workspace)) {
            abort(403);
        }

        if ($project->workspace_id !== $workspace->id) {
            abort(404);
        }

        $project->load(['tasks', 'milestones']);

        return Inertia::render('projects/Show', [
            'workspace' => $workspace,
            'project' => $project,
        ]);
    }

    public function tasks(Workspace $workspace, Project $project): Response
    {
        if (! Gate::allows('view-workspace', $workspace)) {
            abort(403);
        }

        if ($project->workspace_id !== $workspace->id) {
            abort(404);
        }

        $project->load(['tasks.assignee', 'tasks.milestone', 'milestones']);

        return Inertia::render('projects/Tasks', [
            'workspace' => $workspace,
            'project' => $project,
        ]);
    }

    public function storeTask(Request $request, Workspace $workspace, Project $project): RedirectResponse
    {
        if (! Gate::allows('view-workspace', $workspace)) {
            abort(403);
        }

        if ($project->workspace_id !== $workspace->id) {
            abort(404);
        }

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'string', 'in:todo,in_progress,review,done'],
            'priority' => ['required', 'string', 'in:low,medium,high,urgent'],
            'due_date' => ['nullable', 'date'],
        ]);

        $project->tasks()->create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
            'priority' => $request->priority,
            'due_date' => $request->due_date,
            'assignee_id' => $request->user()->id, // Auto-assign to creator for now
        ]);

        return back()->with('success', 'Task created successfully.');
    }

    public function settings(Workspace $workspace, Project $project): Response
    {
        if (! Gate::allows('manage-workspace', $workspace)) {
            abort(403);
        }

        if ($project->workspace_id !== $workspace->id) {
            abort(404);
        }

        return Inertia::render('projects/Settings', [
            'workspace' => $workspace,
            'project' => $project,
        ]);
    }

    public function update(Request $request, Workspace $workspace, Project $project): RedirectResponse
    {
        if (! Gate::allows('manage-workspace', $workspace)) {
            abort(403);
        }

        if ($project->workspace_id !== $workspace->id) {
            abort(404);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'alpha_dash', 'max:255', 'unique:projects,slug,' . $project->id . ',id,workspace_id,' . $workspace->id],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'string', 'in:active,paused,completed,archived'],
        ]);

        $project->update([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return back()->with('success', 'Project updated successfully.');
    }

    public function destroy(Workspace $workspace, Project $project): RedirectResponse
    {
        if (! Gate::allows('manage-workspace', $workspace)) {
            abort(403);
        }

        if ($project->workspace_id !== $workspace->id) {
            abort(404);
        }

        $project->delete();

        return redirect()->route('projects.index', ['workspace' => $workspace->slug])->with('success', 'Project deleted successfully.');
    }
}
