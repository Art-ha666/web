<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/projects/Index', [
            'projects' => Project::query()->ordered()->get(['id', 'title', 'slug', 'industry', 'year', 'featured', 'is_published', 'sort_order']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/projects/Edit', [
            'project' => null,
            'services' => Service::orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Project::create($this->validateData($request));

        return redirect()->route('admin.projects.index')->with('success', 'Project created.');
    }

    public function edit(Project $project): Response
    {
        return Inertia::render('admin/projects/Edit', [
            'project' => $project,
            'services' => Service::orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $project->update($this->validateData($request, $project));

        return redirect()->route('admin.projects.index')->with('success', 'Project saved.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()->route('admin.projects.index')->with('success', 'Project deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateData(Request $request, ?Project $project = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:160', 'alpha_dash', Rule::unique('projects', 'slug')->ignore($project?->id)],
            'client_name' => ['nullable', 'string', 'max:160'],
            'client_type' => ['nullable', 'string', 'max:60'],
            'industry' => ['nullable', 'string', 'max:120'],
            'year' => ['nullable', 'string', 'max:20'],
            'headline_result' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'challenge' => ['nullable', 'string'],
            'approach' => ['nullable', 'string'],
            'architecture_notes' => ['nullable', 'string'],
            'video_url' => ['nullable', 'string', 'max:255'],
            'category_tags' => ['nullable', 'array'],
            'category_tags.*' => ['nullable', 'string', 'max:60'],
            'tech_stack' => ['nullable', 'array'],
            'tech_stack.*' => ['nullable', 'string', 'max:60'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['nullable', 'string', 'max:255'],
            'results' => ['nullable', 'array'],
            'results.*.metric' => ['nullable', 'string', 'max:120'],
            'results.*.label' => ['nullable', 'string', 'max:160'],
            'related_service_id' => ['nullable', Rule::exists('services', 'id')],
            'featured' => ['boolean'],
            'is_published' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'seo_title' => ['nullable', 'string', 'max:160'],
            'seo_description' => ['nullable', 'string', 'max:300'],
        ]);

        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['title']);
        $data['category_tags'] = array_values(array_filter($data['category_tags'] ?? []));
        $data['tech_stack'] = array_values(array_filter($data['tech_stack'] ?? []));
        $data['gallery'] = array_values(array_filter($data['gallery'] ?? []));
        $data['results'] = array_values(array_filter($data['results'] ?? [], fn ($row) => filled($row['metric'] ?? null) || filled($row['label'] ?? null)));
        $data['featured'] = $request->boolean('featured');
        $data['is_published'] = $request->boolean('is_published');

        return $data;
    }
}
