<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GlobalDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        
        // Fetch all tasks assigned to the user across all workspaces
        $myTasks = $user->tasks()
            ->whereIn('status', ['todo', 'in_progress', 'review'])
            ->with(['project.workspace'])
            ->latest()
            ->take(10)
            ->get();

        // Fetch all tasks where the user is a collaborator
        $collaboratorTasks = $user->collaboratorTasks()
            ->whereIn('status', ['todo', 'in_progress', 'review'])
            ->with(['project.workspace'])
            ->latest()
            ->take(10)
            ->get();

        // Merge and sort tasks
        $allTasks = $myTasks->concat($collaboratorTasks)->sortByDesc('created_at')->take(10)->values();

        // Fetch pending approvals for projects where the user is a client
        $pendingApprovals = \App\Models\Task::whereHas('project.users', function ($q) use ($user) {
                $q->where('users.id', $user->id)->wherePivot('role', 'client');
            })
            ->where('requires_approval', true)
            ->where('approval_status', 'pending')
            ->with(['project.workspace'])
            ->latest()
            ->take(10)
            ->get();

        return Inertia::render('Dashboard', [
            'myTasks' => $allTasks,
            'pendingApprovals' => $pendingApprovals,
        ]);
    }
}
