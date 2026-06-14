<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\Theme;
use App\Services\ThemeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DesignController extends Controller
{
    /**
     * Customisable colour token keys exposed to the admin colour picker.
     */
    public const CUSTOMISABLE = ['primary', 'gradFrom', 'gradVia', 'gradTo', 'bg', 'surface', 'text'];

    public function index(): Response
    {
        $settings = SiteSetting::current();

        return Inertia::render('admin/Design', [
            'themes' => Theme::query()->orderBy('sort_order')->get([
                'id', 'key', 'name', 'description', 'tokens', 'hero_variant', 'layout_variant', 'uses_three', 'is_premium',
            ]),
            'activeThemeId' => $settings->active_theme_id,
            'customTokens' => $settings->custom_tokens ?? [],
            'customisable' => self::CUSTOMISABLE,
        ]);
    }

    public function activate(Theme $theme, ThemeService $themes): RedirectResponse
    {
        $settings = SiteSetting::current();
        $settings->update(['active_theme_id' => $theme->id]);
        $themes->flush();

        return back()->with('success', "“{$theme->name}” is now your live design.");
    }

    public function customize(Request $request, ThemeService $themes): RedirectResponse
    {
        $validated = $request->validate([
            'tokens' => ['array'],
            'tokens.*' => ['nullable', 'string', 'max:64'],
        ]);

        $tokens = collect($validated['tokens'] ?? [])
            ->only(self::CUSTOMISABLE)
            ->filter(fn ($value) => filled($value))
            ->all();

        $settings = SiteSetting::current();
        $settings->update(['custom_tokens' => $tokens ?: null]);
        $themes->flush();

        return back()->with('success', 'Colour palette updated.');
    }

    public function reset(ThemeService $themes): RedirectResponse
    {
        SiteSetting::current()->update(['custom_tokens' => null]);
        $themes->flush();

        return back()->with('success', 'Colours reset to the design default.');
    }
}
