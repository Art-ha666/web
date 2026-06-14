<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class JobPostingController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/jobs/Index', [
            'jobPostings' => JobPosting::query()
                ->orderBy('sort_order')
                ->orderByDesc('posted_at')
                ->get(['id', 'title', 'slug', 'department', 'is_open', 'sort_order']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/jobs/Edit', [
            'jobPosting' => null,
            'employmentTypes' => $this->employmentTypes(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        JobPosting::create($this->validateData($request));

        return redirect()->route('admin.jobs.index')->with('success', 'Job posting created.');
    }

    public function edit(JobPosting $jobPosting): Response
    {
        return Inertia::render('admin/jobs/Edit', [
            'jobPosting' => $jobPosting,
            'employmentTypes' => $this->employmentTypes(),
        ]);
    }

    public function update(Request $request, JobPosting $jobPosting): RedirectResponse
    {
        $jobPosting->update($this->validateData($request, $jobPosting));

        return redirect()->route('admin.jobs.index')->with('success', 'Job posting saved.');
    }

    public function destroy(JobPosting $jobPosting): RedirectResponse
    {
        $jobPosting->delete();

        return redirect()->route('admin.jobs.index')->with('success', 'Job posting deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateData(Request $request, ?JobPosting $jobPosting = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:160', 'alpha_dash', Rule::unique('job_postings', 'slug')->ignore($jobPosting?->id)],
            'department' => ['nullable', 'string', 'max:120'],
            'location' => ['nullable', 'string', 'max:120'],
            'employment_type' => ['nullable', Rule::in($this->employmentTypes())],
            'seniority' => ['nullable', 'string', 'max:60'],
            'summary' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string'],
            'responsibilities' => ['nullable', 'array'],
            'responsibilities.*' => ['nullable', 'string', 'max:300'],
            'requirements' => ['nullable', 'array'],
            'requirements.*' => ['nullable', 'string', 'max:300'],
            'tech_stack' => ['nullable', 'array'],
            'tech_stack.*' => ['nullable', 'string', 'max:60'],
            'salary_range' => ['nullable', 'string', 'max:120'],
            'is_open' => ['boolean'],
            'apply_url' => ['nullable', 'url', 'max:255'],
            'posted_at' => ['nullable', 'date'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['title']);
        $data['responsibilities'] = array_values(array_filter($data['responsibilities'] ?? []));
        $data['requirements'] = array_values(array_filter($data['requirements'] ?? []));
        $data['tech_stack'] = array_values(array_filter($data['tech_stack'] ?? []));
        $data['is_open'] = $request->boolean('is_open');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }

    /**
     * @return list<string>
     */
    protected function employmentTypes(): array
    {
        return ['Full-time', 'Part-time', 'Contract', 'Internship', 'Temporary'];
    }
}
