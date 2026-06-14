<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CtaSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CtaSectionController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/ctas/Index', [
            'ctaSections' => CtaSection::query()->orderBy('key')->get(['id', 'key', 'headline', 'is_active']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/ctas/Edit', ['ctaSection' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        CtaSection::create($this->validateData($request));

        return redirect()->route('admin.cta-sections.index')->with('success', 'CTA section created.');
    }

    public function edit(CtaSection $ctaSection): Response
    {
        return Inertia::render('admin/ctas/Edit', ['ctaSection' => $ctaSection]);
    }

    public function update(Request $request, CtaSection $ctaSection): RedirectResponse
    {
        $ctaSection->update($this->validateData($request, $ctaSection));

        return redirect()->route('admin.cta-sections.index')->with('success', 'CTA section saved.');
    }

    public function destroy(CtaSection $ctaSection): RedirectResponse
    {
        $ctaSection->delete();

        return redirect()->route('admin.cta-sections.index')->with('success', 'CTA section deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateData(Request $request, ?CtaSection $ctaSection = null): array
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:120', Rule::unique('cta_sections', 'key')->ignore($ctaSection?->id)],
            'eyebrow' => ['nullable', 'string', 'max:120'],
            'headline' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'primary_cta_label' => ['nullable', 'string', 'max:120'],
            'primary_cta_url' => ['nullable', 'string', 'max:255'],
            'secondary_cta_label' => ['nullable', 'string', 'max:120'],
            'secondary_cta_url' => ['nullable', 'string', 'max:255'],
            'microcopy' => ['nullable', 'string', 'max:255'],
            'gradient_variant' => ['nullable', 'string', Rule::in(['default', 'aurora', 'sunset', 'ocean', 'violet'])],
            'is_active' => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
