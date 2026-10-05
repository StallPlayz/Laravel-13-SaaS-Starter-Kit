<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ServiceRequestController extends Controller
{
    public function index(Request $request, Workspace $workspace): Response
    {
        if (! Gate::allows('view-workspace', $workspace)) {
            abort(403);
        }

        $canManage = Gate::allows('manage-workspace', $workspace);

        $query = $workspace->serviceRequests()->with('client')->latest();

        if (!$canManage) {
            $query->where('client_id', $request->user()->id);
        }

        $serviceRequests = $query->get();

        return Inertia::render('service-requests/Index', [
            'workspace' => $workspace,
            'serviceRequests' => $serviceRequests,
        ]);
    }

    public function store(Request $request, Workspace $workspace): RedirectResponse
    {
        if (! Gate::allows('view-workspace', $workspace)) {
            abort(403);
        }

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'type' => ['required', 'string', 'in:project,task,general'],
        ]);

        $workspace->serviceRequests()->create([
            'client_id' => $request->user()->id,
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Service request submitted successfully.');
    }

    public function updateStatus(Request $request, Workspace $workspace, ServiceRequest $serviceRequest): RedirectResponse
    {
        if (! Gate::allows('manage-workspace', $workspace)) {
            abort(403);
        }

        if ($serviceRequest->workspace_id !== $workspace->id) {
            abort(404);
        }

        $request->validate([
            'status' => ['required', 'string', 'in:pending,reviewed,converted,rejected'],
        ]);

        $serviceRequest->update([
            'status' => $request->status,
        ]);

        return back()->with('success', 'Service request status updated.');
    }
}
