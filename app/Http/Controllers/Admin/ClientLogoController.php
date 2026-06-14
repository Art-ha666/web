<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientLogo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ClientLogoController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/logos/Index', [
            'clientLogos' => ClientLogo::query()->orderBy('sort_order')->get(['id', 'name', 'logo_path', 'url', 'type', 'is_active', 'sort_order']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/logos/Edit', ['clientLogo' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        ClientLogo::create($this->validateData($request));

        return redirect()->route('admin.client-logos.index')->with('success', 'Client logo created.');
    }

    public function edit(ClientLogo $clientLogo): Response
    {
        return Inertia::render('admin/logos/Edit', ['clientLogo' => $clientLogo]);
    }

    public function update(Request $request, ClientLogo $clientLogo): RedirectResponse
    {
        $clientLogo->update($this->validateData($request, $clientLogo));

        return redirect()->route('admin.client-logos.index')->with('success', 'Client logo saved.');
    }

    public function destroy(ClientLogo $clientLogo): RedirectResponse
    {
        $clientLogo->delete();

        return redirect()->route('admin.client-logos.index')->with('success', 'Client logo deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateData(Request $request, ?ClientLogo $clientLogo = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'logo_path' => ['nullable', 'string', 'max:255'],
            'logo_svg' => ['nullable', 'string'],
            'url' => ['nullable', 'string', 'max:255'],
            'type' => ['required', Rule::in(['client', 'tech_partner'])],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
