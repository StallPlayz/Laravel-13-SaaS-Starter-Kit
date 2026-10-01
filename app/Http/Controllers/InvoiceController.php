<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function index(Request $request, Workspace $workspace): Response
    {
        if (! Gate::allows('view-workspace', $workspace)) {
            abort(403);
        }

        $canManage = Gate::allows('manage-workspace', $workspace);

        $query = $workspace->invoices()->with('project')->latest();

        if (!$canManage) {
            $query->where('client_id', $request->user()->id);
        }

        $invoices = $query->get();

        return Inertia::render('invoices/Index', [
            'workspace' => $workspace,
            'invoices' => $invoices,
        ]);
    }

    public function create(Workspace $workspace): Response
    {
        if (! Gate::allows('manage-workspace', $workspace)) {
            abort(403);
        }

        $projects = $workspace->projects()->get();
        $clients = $workspace->users()->wherePivot('role', 'client')->get();

        return Inertia::render('invoices/Create', [
            'workspace' => $workspace,
            'projects' => $projects,
            'clients' => $clients,
        ]);
    }

    public function store(Request $request, Workspace $workspace): RedirectResponse
    {
        if (! Gate::allows('manage-workspace', $workspace)) {
            abort(403);
        }

        $request->validate([
            'client_id' => ['nullable', 'exists:users,id'],
            'client_name' => ['required', 'string', 'max:255'],
            'client_email' => ['nullable', 'email', 'max:255'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $subtotal = 0;
        foreach ($request->items as $item) {
            $subtotal += $item['quantity'] * $item['unit_price'];
        }

        $tax = 0; // Can be calculated based on settings later
        $total = $subtotal + $tax;

        $invoice = $workspace->invoices()->create([
            'client_id' => $request->client_id,
            'client_name' => $request->client_name,
            'client_email' => $request->client_email,
            'project_id' => $request->project_id,
            'issue_date' => $request->issue_date,
            'due_date' => $request->due_date,
            'notes' => $request->notes,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'status' => 'draft',
        ]);

        foreach ($request->items as $item) {
            $invoice->items()->create([
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        return redirect()->route('invoices.show', ['workspace' => $workspace->slug, 'invoice' => $invoice->id])
            ->with('success', 'Invoice created successfully.');
    }

    public function show(Request $request, Workspace $workspace, Invoice $invoice): Response
    {
        if (! Gate::allows('view-workspace', $workspace)) {
            abort(403);
        }

        if ($invoice->workspace_id !== $workspace->id) {
            abort(404);
        }

        $canManage = Gate::allows('manage-workspace', $workspace);
        if (!$canManage && $invoice->client_id !== $request->user()->id) {
            abort(403, 'You can only view your own invoices.');
        }

        $invoice->load(['items', 'project']);

        return Inertia::render('invoices/Show', [
            'workspace' => $workspace,
            'invoice' => $invoice,
        ]);
    }

    public function updateStatus(Request $request, Workspace $workspace, Invoice $invoice): RedirectResponse
    {
        if (! Gate::allows('manage-workspace', $workspace)) {
            abort(403);
        }

        if ($invoice->workspace_id !== $workspace->id) {
            abort(404);
        }

        $request->validate([
            'status' => ['required', 'string', 'in:draft,sent,paid,overdue,cancelled'],
        ]);

        $invoice->update([
            'status' => $request->status,
        ]);

        return back()->with('success', 'Invoice status updated.');
    }
}
