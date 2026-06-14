<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NavItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class NavItemController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/nav/Index', [
            'navItems' => NavItem::query()
                ->orderBy('sort_order')
                ->get(['id', 'label', 'location', 'url', 'is_cta', 'is_active', 'sort_order']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/nav/Edit', [
            'navItem' => null,
            'parentOptions' => $this->parentOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        NavItem::create($this->validateData($request));

        return redirect()->route('admin.nav-items.index')->with('success', 'Nav item created.');
    }

    public function edit(NavItem $navItem): Response
    {
        return Inertia::render('admin/nav/Edit', [
            'navItem' => $navItem,
            'parentOptions' => $this->parentOptions($navItem),
        ]);
    }

    public function update(Request $request, NavItem $navItem): RedirectResponse
    {
        $navItem->update($this->validateData($request, $navItem));

        return redirect()->route('admin.nav-items.index')->with('success', 'Nav item saved.');
    }

    public function destroy(NavItem $navItem): RedirectResponse
    {
        $navItem->delete();

        return redirect()->route('admin.nav-items.index')->with('success', 'Nav item deleted.');
    }

    /**
     * @return array<int, array{id: int, label: string}>
     */
    protected function parentOptions(?NavItem $navItem = null): array
    {
        return NavItem::query()
            ->when($navItem, fn ($query) => $query->whereKeyNot($navItem->id))
            ->orderBy('label')
            ->get(['id', 'label'])
            ->map(fn (NavItem $item) => ['id' => $item->id, 'label' => $item->label])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateData(Request $request, ?NavItem $navItem = null): array
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:120'],
            'url' => ['nullable', 'string', 'max:255'],
            'location' => ['required', Rule::in(['header', 'footer'])],
            'mega_group' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:60'],
            'parent_id' => ['nullable', 'integer', Rule::exists('nav_items', 'id')],
            'is_cta' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['is_cta'] = $request->boolean('is_cta');
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
