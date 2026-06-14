<?php

namespace App\Services;

use App\Models\SiteSetting;
use App\Models\Theme;
use Illuminate\Support\Facades\Cache;
use Throwable;

class ThemeService
{
    /**
     * Maps internal token keys to the public CSS custom property names
     * that the Vue components and stylesheet reference.
     *
     * @var array<string, string>
     */
    public const VAR_MAP = [
        'bg' => '--ak-bg',
        'bg2' => '--ak-bg-2',
        'surface' => '--ak-surface',
        'surface2' => '--ak-surface-2',
        'glass' => '--ak-glass',
        'glassBorder' => '--ak-glass-border',
        'hairline' => '--ak-hairline',
        'text' => '--ak-text',
        'textSecondary' => '--ak-text-2',
        'textMuted' => '--ak-text-3',
        'primary' => '--ak-primary',
        'primaryHover' => '--ak-primary-hover',
        'onPrimary' => '--ak-on-primary',
        'gradFrom' => '--ak-grad-1',
        'gradVia' => '--ak-grad-2',
        'gradTo' => '--ak-grad-3',
        'ring' => '--ak-ring',
        'glow' => '--ak-glow',
        'fontDisplay' => '--ak-font-display',
        'fontBody' => '--ak-font-body',
        'fontMono' => '--ak-font-mono',
        'radius' => '--ak-radius',
        'navBg' => '--ak-nav-bg',
        'footerBg' => '--ak-footer-bg',
    ];

    public const CACHE_KEY = 'ak.active_theme';

    /**
     * Resolve the active theme as a normalized array with merged custom tokens.
     *
     * @return array{key:string,name:string,hero_variant:string,layout_variant:string,uses_three:bool,scheme:string,tokens:array<string,string>}
     */
    public function active(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn (): array => $this->resolve());
    }

    /**
     * @return array{key:string,name:string,hero_variant:string,layout_variant:string,uses_three:bool,scheme:string,tokens:array<string,string>}
     */
    protected function resolve(): array
    {
        try {
            $settings = SiteSetting::query()->first();
            $theme = $settings?->active_theme_id
                ? Theme::query()->find($settings->active_theme_id)
                : Theme::query()->orderBy('sort_order')->first();

            if ($theme) {
                $tokens = array_merge(
                    is_array($theme->tokens) ? $theme->tokens : [],
                    is_array($settings?->custom_tokens) ? $settings->custom_tokens : [],
                );

                return [
                    'key' => $theme->key,
                    'name' => $theme->name,
                    'hero_variant' => $theme->hero_variant,
                    'layout_variant' => $theme->layout_variant,
                    'uses_three' => (bool) $theme->uses_three,
                    'scheme' => $tokens['scheme'] ?? 'dark',
                    'tokens' => $tokens,
                ];
            }
        } catch (Throwable) {
            // Database not ready (fresh install / migration) - fall back to config default.
        }

        return $this->fromConfig('aurora');
    }

    /**
     * @return array{key:string,name:string,hero_variant:string,layout_variant:string,uses_three:bool,scheme:string,tokens:array<string,string>}
     */
    protected function fromConfig(string $key): array
    {
        /** @var array<string, mixed> $preset */
        $preset = config("themes.$key", []);
        $tokens = (array) ($preset['tokens'] ?? []);

        return [
            'key' => $key,
            'name' => $preset['name'] ?? 'Aurora',
            'hero_variant' => $preset['hero_variant'] ?? 'aurora',
            'layout_variant' => $preset['layout_variant'] ?? 'standard',
            'uses_three' => (bool) ($preset['uses_three'] ?? false),
            'scheme' => $tokens['scheme'] ?? 'dark',
            'tokens' => $tokens,
        ];
    }

    /**
     * Build the `:root { --ak-*: ...; }` declaration for the active theme.
     */
    public function cssVariables(): string
    {
        $tokens = $this->active()['tokens'];
        $lines = [];

        foreach (self::VAR_MAP as $key => $var) {
            if (isset($tokens[$key]) && $tokens[$key] !== '') {
                $lines[] = "{$var}:{$tokens[$key]};";
            }
        }

        return ':root{'.implode('', $lines).'}';
    }

    /**
     * The minimal theme meta shared with the frontend via Inertia.
     *
     * @return array{key:string,name:string,hero:string,layout:string,usesThree:bool,scheme:string,tokens:array<string,string>}
     */
    public function meta(): array
    {
        $active = $this->active();

        return [
            'key' => $active['key'],
            'name' => $active['name'],
            'hero' => $active['hero_variant'],
            'layout' => $active['layout_variant'],
            'usesThree' => $active['uses_three'],
            'scheme' => $active['scheme'],
            'tokens' => $active['tokens'],
        ];
    }

    /**
     * Forget the cached active theme (call after admin updates theme/settings).
     */
    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
