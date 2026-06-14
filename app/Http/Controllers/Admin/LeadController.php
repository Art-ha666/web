<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class LeadController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->string('status')->toString();

        $leads = Lead::query()
            ->when(in_array($status, Lead::STATUSES, true), fn ($q) => $q->where('status', $status))
            ->latest()
            ->get();

        return Inertia::render('admin/leads/Index', [
            'leads' => $leads,
            'statuses' => Lead::STATUSES,
            'filter' => $status,
            'counts' => [
                'all' => Lead::count(),
                'new' => Lead::where('status', 'new')->count(),
            ],
        ]);
    }

    public function show(Lead $lead): Response
    {
        return Inertia::render('admin/leads/Show', [
            'lead' => $lead,
            'statuses' => Lead::STATUSES,
        ]);
    }

    public function update(Request $request, Lead $lead): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(Lead::STATUSES)],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $lead->update($validated);

        return back()->with('success', 'Lead updated.');
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        $lead->delete();

        return redirect()->route('admin.leads.index')->with('success', 'Lead deleted.');
    }
}
