<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProcessStep;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProcessStepController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/process/Index', [
            'processSteps' => ProcessStep::query()->ordered()->get(['id', 'number', 'title', 'deliverable_tag', 'icon', 'is_active', 'sort_order']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/process/Edit', ['processStep' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        ProcessStep::create($this->validateData($request));

        return redirect()->route('admin.process-steps.index')->with('success', 'Process step created.');
    }

    public function edit(ProcessStep $processStep): Response
    {
        return Inertia::render('admin/process/Edit', ['processStep' => $processStep]);
    }

    public function update(Request $request, ProcessStep $processStep): RedirectResponse
    {
        $processStep->update($this->validateData($request, $processStep));

        return redirect()->route('admin.process-steps.index')->with('success', 'Process step saved.');
    }

    public function destroy(ProcessStep $processStep): RedirectResponse
    {
        $processStep->delete();

        return redirect()->route('admin.process-steps.index')->with('success', 'Process step deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateData(Request $request, ?ProcessStep $processStep = null): array
    {
        $data = $request->validate([
            'number' => ['nullable', 'string', 'max:10'],
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'deliverable_tag' => ['nullable', 'string', 'max:120'],
            'icon' => ['nullable', 'string', 'max:60'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
