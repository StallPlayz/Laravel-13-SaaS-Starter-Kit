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
    public function index(Workspace $workspace): Response
    {
        if (! Gate::allows('manage-workspace', $workspace)) {
            abort(403);
        }

        $invoices = $workspace->invoices()->with('project')->latest()->get();

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

        return Inertia::render('invoices/Create', [
            'workspace' => $workspace,
            'projects' => $projects,
        ]);
    }

    public function store(Request $request, Workspace $workspace): RedirectResponse
    {
        if (! Gate::allows('manage-workspace', $workspace)) {
            abort(403);
        }

        $request->validate([
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

    public function show(Workspace $workspace, Invoice $invoice): Response
    {
        if (! Gate::allows('manage-workspace', $workspace)) {
            abort(403);
        }

        if ($invoice->workspace_id !== $workspace->id) {
            abort(404);
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
