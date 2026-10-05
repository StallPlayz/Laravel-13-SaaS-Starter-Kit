<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(Request $request, Workspace $workspace): Response
    {
        if (! Gate::allows('view-workspace', $workspace)) {
            abort(403);
        }

        $canManage = Gate::allows('manage-workspace', $workspace);

        $query = $workspace->projects()->latest();

        if (!$canManage) {
            $query->where('client_id', $request->user()->id);
        }

        $projects = $query->get();

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

        $clients = $workspace->users()->wherePivot('role', 'client')->get();

        return Inertia::render('projects/Create', [
            'workspace' => $workspace,
            'clients' => $clients,
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
            'client_id' => ['nullable', 'exists:users,id'],
        ]);

        $project = $workspace->projects()->create([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'client_id' => $request->client_id,
            'status' => 'active',
        ]);

        return redirect()->route('projects.show', ['workspace' => $workspace->slug, 'project' => $project->slug]);
    }

    public function show(Request $request, Workspace $workspace, Project $project): Response
    {
        if (! Gate::allows('view-workspace', $workspace)) {
            abort(403);
        }

        if ($project->workspace_id !== $workspace->id) {
            abort(404);
        }

        $canManage = Gate::allows('manage-workspace', $workspace);
        if (!$canManage && $project->client_id !== $request->user()->id) {
            abort(403, 'You can only view your own projects.');
        }

        $project->load(['tasks', 'milestones']);

        return Inertia::render('projects/Show', [
            'workspace' => $workspace,
            'project' => $project,
        ]);
    }

    public function tasks(Request $request, Workspace $workspace, Project $project): Response
    {
        if (! Gate::allows('view-workspace', $workspace)) {
            abort(403);
        }

        if ($project->workspace_id !== $workspace->id) {
            abort(404);
        }

        $canManage = Gate::allows('manage-workspace', $workspace);
        if (!$canManage && $project->client_id !== $request->user()->id) {
            abort(403, 'You can only view tasks for your own projects.');
        }

        $project->load(['tasks.assignee', 'tasks.milestone', 'milestones']);
        $members = $workspace->users()->get(['users.id', 'users.name', 'users.email']);

        return Inertia::render('projects/Tasks', [
            'workspace' => $workspace,
            'project' => $project,
            'members' => $members,
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

        $canManage = Gate::allows('manage-workspace', $workspace);

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'string', 'in:todo,in_progress,review,done'],
            'priority' => ['required', 'string', 'in:low,medium,high,urgent'],
            'due_date' => ['nullable', 'date'],
        ];

        if ($canManage) {
            $rules['assignee_id'] = ['nullable', 'exists:users,id'];
        }

        $request->validate($rules);

        $project->tasks()->create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
            'priority' => $request->priority,
            'due_date' => $request->due_date,
            'assignee_id' => $canManage ? $request->assignee_id : $request->user()->id,
        ]);

        return back()->with('success', 'Task created successfully.');
    }

    public function updateTask(Request $request, Workspace $workspace, Project $project, Task $task): RedirectResponse
    {
        if (! Gate::allows('view-workspace', $workspace)) {
            abort(403);
        }

        if ($project->workspace_id !== $workspace->id || $task->project_id !== $project->id) {
            abort(404);
        }

        $canManage = Gate::allows('manage-workspace', $workspace);
        if (!$canManage && $task->assignee_id !== $request->user()->id) {
            abort(403, 'You can only update tasks assigned to you.');
        }

        $request->validate([
            'status' => ['sometimes', 'required', 'string', 'in:todo,in_progress,review,done'],
            'requires_approval' => ['sometimes', 'boolean'],
            'approval_status' => ['sometimes', 'required', 'string', 'in:pending,approved,rejected'],
        ]);

        $updates = [];
        if ($request->has('status')) {
            $updates['status'] = $request->status;
        }
        if ($request->has('requires_approval')) {
            $updates['requires_approval'] = $request->requires_approval;
        }
        if ($request->has('approval_status')) {
            $updates['approval_status'] = $request->approval_status;
        }

        $task->update($updates);

        return back()->with('success', 'Task updated successfully.');
    }

    public function approveTask(Request $request, Workspace $workspace, Project $project, Task $task): RedirectResponse
    {
        if (! Gate::allows('view-workspace', $workspace)) {
            abort(403);
        }

        if ($project->workspace_id !== $workspace->id || $task->project_id !== $project->id) {
            abort(404);
        }

        // Only the assigned client can approve/reject
        if ($project->client_id !== $request->user()->id) {
            abort(403, 'Only the client assigned to this project can approve tasks.');
        }

        $request->validate([
            'approval_status' => ['required', 'string', 'in:approved,rejected'],
            'approval_feedback' => ['nullable', 'string'],
        ]);

        $updates = [
            'approval_status' => $request->approval_status,
            'approval_feedback' => $request->approval_feedback,
        ];

        // If rejected, automatically move it back to in_progress
        if ($request->approval_status === 'rejected') {
            $updates['status'] = 'in_progress';
        }

        // If approved, automatically move it to done
        if ($request->approval_status === 'approved') {
            $updates['status'] = 'done';
        }

        $task->update($updates);

        return back()->with('success', 'Task approval submitted.');
    }

    public function settings(Workspace $workspace, Project $project): Response
    {
        if (! Gate::allows('manage-workspace', $workspace)) {
            abort(403);
        }

        if ($project->workspace_id !== $workspace->id) {
            abort(404);
        }

        $clients = $workspace->users()->wherePivot('role', 'client')->get();

        return Inertia::render('projects/Settings', [
            'workspace' => $workspace,
            'project' => $project,
            'clients' => $clients,
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
            'client_id' => ['nullable', 'exists:users,id'],
        ]);

        $project->update([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'status' => $request->status,
            'client_id' => $request->client_id,
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
