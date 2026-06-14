<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/services/Index', [
            'services' => Service::query()->ordered()->get(['id', 'title', 'slug', 'icon', 'value_metric', 'featured', 'is_active', 'sort_order']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/services/Edit', ['service' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        Service::create($this->validateData($request));

        return redirect()->route('admin.services.index')->with('success', 'Service created.');
    }

    public function edit(Service $service): Response
    {
        return Inertia::render('admin/services/Edit', ['service' => $service]);
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $service->update($this->validateData($request, $service));

        return redirect()->route('admin.services.index')->with('success', 'Service saved.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();

        return redirect()->route('admin.services.index')->with('success', 'Service deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateData(Request $request, ?Service $service = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:120', 'alpha_dash', Rule::unique('services', 'slug')->ignore($service?->id)],
            'icon' => ['nullable', 'string', 'max:60'],
            'tab_label' => ['nullable', 'string', 'max:60'],
            'short_blurb' => ['nullable', 'string', 'max:255'],
            'intro' => ['nullable', 'string', 'max:1000'],
            'value_metric' => ['nullable', 'string', 'max:120'],
            'benefit_bullets' => ['nullable', 'array'],
            'benefit_bullets.*' => ['nullable', 'string', 'max:200'],
            'detail_body' => ['nullable', 'string'],
            'gradient' => ['nullable', 'array'],
            'gradient.from' => ['nullable', 'string', 'max:32'],
            'gradient.via' => ['nullable', 'string', 'max:32'],
            'gradient.to' => ['nullable', 'string', 'max:32'],
            'tech_stack' => ['nullable', 'array'],
            'tech_stack.*' => ['nullable', 'string', 'max:60'],
            'featured' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'seo_title' => ['nullable', 'string', 'max:160'],
            'seo_description' => ['nullable', 'string', 'max:300'],
        ]);

        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['title']);
        $data['benefit_bullets'] = array_values(array_filter($data['benefit_bullets'] ?? []));
        $data['tech_stack'] = array_values(array_filter($data['tech_stack'] ?? []));
        $data['featured'] = $request->boolean('featured');
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
