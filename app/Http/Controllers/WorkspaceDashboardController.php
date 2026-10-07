<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class WorkspaceDashboardController extends Controller
{
    public function index(Request $request, Workspace $workspace): Response
    {
        if (! Gate::allows('view-workspace', $workspace)) {
            abort(403);
        }

        $user = $request->user();
        $role = $user->getWorkspaceRole($workspace);

        $data = [
            'workspace' => $workspace,
            'role' => $role,
        ];

        if ($role === 'owner' || $role === 'admin') {
            $data['stats'] = [
                'active_projects' => $workspace->projects()->where('status', 'active')->count(),
                'pending_requests' => $workspace->serviceRequests()->where('status', 'pending')->count(),
                'outstanding_invoices' => $workspace->invoices()->whereIn('status', ['draft', 'sent', 'overdue'])->sum('total'),
            ];
            $data['recent_requests'] = $workspace->serviceRequests()->with('client')->latest()->take(5)->get();
        } elseif ($role === 'member') {
            $data['my_tasks'] = $workspace->tasks()
                ->where(function ($query) use ($user) {
                    $query->where('assignee_id', $user->id)
                          ->orWhereHas('collaborators', function ($q) use ($user) {
                              $q->where('users.id', $user->id);
                          });
                })
                ->whereIn('status', ['todo', 'in_progress', 'review'])
                ->with('project')
                ->latest()
                ->take(10)
                ->get();
        } elseif ($role === 'client') {
            $data['active_projects'] = $workspace->projects()
                ->whereHas('users', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                })
                ->where('status', 'active')
                ->withCount(['tasks as completed_tasks_count' => function ($query) {
                    $query->where('status', 'done');
                }])
                ->withCount('tasks as total_tasks_count')
                ->get();
                
            $data['pending_approvals'] = $workspace->tasks()
                ->whereHas('project.users', function ($q) use ($user) {
                    $q->where('users.id', $user->id)->wherePivot('role', 'client');
                })
                ->where('requires_approval', true)
                ->where('approval_status', 'pending')
                ->with('project')
                ->get();
        }

        return Inertia::render('workspaces/Dashboard', $data);
    }
}
