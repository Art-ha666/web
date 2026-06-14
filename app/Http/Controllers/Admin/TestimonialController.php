<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TestimonialController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/testimonials/Index', [
            'testimonials' => Testimonial::query()->orderBy('sort_order')->get(['id', 'author_name', 'author_role', 'company_name', 'rating', 'featured', 'is_active', 'sort_order']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/testimonials/Edit', [
            'testimonial' => null,
            'projects' => Project::orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Testimonial::create($this->validateData($request));

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial created.');
    }

    public function edit(Testimonial $testimonial): Response
    {
        return Inertia::render('admin/testimonials/Edit', [
            'testimonial' => $testimonial,
            'projects' => Project::orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function update(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $testimonial->update($this->validateData($request, $testimonial));

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial saved.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->delete();

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateData(Request $request, ?Testimonial $testimonial = null): array
    {
        $data = $request->validate([
            'quote' => ['required', 'string'],
            'author_name' => ['required', 'string', 'max:120'],
            'author_role' => ['nullable', 'string', 'max:120'],
            'company_name' => ['nullable', 'string', 'max:120'],
            'company_logo' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'string', 'max:255'],
            'project_id' => ['nullable', Rule::exists('projects', 'id')],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'featured' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['featured'] = $request->boolean('featured');
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
