<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Stat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class StatController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/stats/Index', [
            'stats' => Stat::query()->ordered()->get(['id', 'value', 'prefix', 'suffix', 'label', 'group', 'accent_color', 'is_active', 'sort_order']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/stats/Edit', ['stat' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        Stat::create($this->validateData($request));

        return redirect()->route('admin.stats.index')->with('success', 'Stat created.');
    }

    public function edit(Stat $stat): Response
    {
        return Inertia::render('admin/stats/Edit', ['stat' => $stat]);
    }

    public function update(Request $request, Stat $stat): RedirectResponse
    {
        $stat->update($this->validateData($request, $stat));

        return redirect()->route('admin.stats.index')->with('success', 'Stat saved.');
    }

    public function destroy(Stat $stat): RedirectResponse
    {
        $stat->delete();

        return redirect()->route('admin.stats.index')->with('success', 'Stat deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateData(Request $request, ?Stat $stat = null): array
    {
        $data = $request->validate([
            'value' => ['required', 'string', 'max:60'],
            'prefix' => ['nullable', 'string', 'max:16'],
            'suffix' => ['nullable', 'string', 'max:16'],
            'label' => ['required', 'string', 'max:120'],
            'group' => ['required', Rule::in(['band', 'hero'])],
            'accent_color' => ['nullable', 'string', 'max:32'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
