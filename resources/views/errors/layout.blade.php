<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    @php
        // Brand + contact come from settings. Wrapped in try/catch so a 500
        // (e.g. DB down) still renders with safe fallbacks.
        try {
            $akSettings = \App\Models\SiteSetting::current();
        } catch (\Throwable $e) {
            $akSettings = null;
        }
        $akBrand = $akSettings->site_name ?? config('app.name', 'AKH Solutions');
        $akEmail = $akSettings->primary_email ?? null;
    @endphp
    <title>@yield('title', 'Error') · {{ $akBrand }}</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    @php
        // Match the live design. Wrapped in try/catch so a 500 (e.g. DB down)
        // still renders a styled page using safe fallback tokens.
        try {
            $akVars = app(\App\Services\ThemeService::class)->cssVariables();
        } catch (\Throwable $e) {
            $akVars = ':root{--ak-bg:#07080d;--ak-bg-2:#0b0d14;--ak-surface:#10131c;--ak-text:#f3f5fb;--ak-text-2:#aab2c5;--ak-text-3:#727a8e;--ak-primary:#5778f8;--ak-on-primary:#ffffff;--ak-grad-1:#28baf3;--ak-grad-2:#5778f8;--ak-grad-3:#7e2cfd;--ak-glass-border:rgba(255,255,255,0.08);}';
        }
    @endphp
    <style>
        {!! $akVars !!}
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body {
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
            background: var(--ak-bg, #07080d); color: var(--ak-text, #f3f5fb);
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            text-align: center; padding: 24px; position: relative; overflow: hidden;
            -webkit-font-smoothing: antialiased;
        }
        .ak-bg { position: absolute; inset: 0; z-index: 0; pointer-events: none;
            background:
                radial-gradient(900px 520px at 72% -12%, color-mix(in srgb, var(--ak-grad-2) 26%, transparent), transparent 60%),
                radial-gradient(720px 480px at 8% 112%, color-mix(in srgb, var(--ak-grad-3) 20%, transparent), transparent 60%);
        }
        .ak-grid { position: absolute; inset: 0; z-index: 0; opacity: .35; pointer-events: none;
            background-image:
                linear-gradient(color-mix(in srgb, var(--ak-text) 6%, transparent) 1px, transparent 1px),
                linear-gradient(90deg, color-mix(in srgb, var(--ak-text) 6%, transparent) 1px, transparent 1px);
            background-size: 44px 44px; mask-image: radial-gradient(circle at 50% 40%, black, transparent 70%);
        }
        .ak-card { position: relative; z-index: 1; max-width: 580px; }
        .ak-brand { display: inline-flex; align-items: center; gap: 10px; margin-bottom: 30px; font-weight: 700; letter-spacing: -.01em; font-size: 18px; }
        .ak-dot { width: 28px; height: 28px; border-radius: 9px; background: linear-gradient(120deg, var(--ak-grad-1), var(--ak-grad-3)); }
        .ak-eyebrow { letter-spacing: .2em; text-transform: uppercase; font-size: 12px; font-weight: 600; color: var(--ak-text-3, #727a8e); }
        .ak-code { font-size: clamp(96px, 22vw, 196px); line-height: 1; font-weight: 800; letter-spacing: -.04em; margin: 6px 0 4px;
            background: linear-gradient(120deg, var(--ak-grad-1), var(--ak-grad-2), var(--ak-grad-3));
            -webkit-background-clip: text; background-clip: text; color: transparent; }
        h1 { font-size: clamp(22px, 4vw, 32px); font-weight: 700; margin: 0 0 12px; letter-spacing: -.02em; }
        p { color: var(--ak-text-2, #aab2c5); font-size: 17px; line-height: 1.6; margin: 0 auto 30px; max-width: 44ch; }
        .ak-actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
        a.ak-btn { display: inline-flex; align-items: center; gap: 8px; padding: 13px 24px; border-radius: 12px; font-weight: 600; font-size: 15px; text-decoration: none; transition: .2s; }
        .ak-primary { background: linear-gradient(120deg, var(--ak-grad-1), var(--ak-grad-2), var(--ak-grad-3)); color: #fff; }
        .ak-primary:hover { opacity: .92; transform: translateY(-1px); }
        .ak-ghost { border: 1px solid var(--ak-glass-border, rgba(255,255,255,.12)); color: var(--ak-text, #f3f5fb); }
        .ak-ghost:hover { border-color: var(--ak-primary, #5778f8); }
    </style>
</head>
<body>
    <div class="ak-bg"></div>
    <div class="ak-grid"></div>
    <div class="ak-card">
        <div class="ak-brand"><span class="ak-dot"></span> {{ $akBrand }}</div>
        <p class="ak-eyebrow">@yield('eyebrow', 'Error')</p>
        <div class="ak-code">@yield('code', '!')</div>
        <h1>@yield('heading', 'Something went wrong.')</h1>
        <p>@yield('message')</p>
        <div class="ak-actions">
            <a class="ak-btn ak-primary" href="/">Back to home</a>
            @if (filled($akEmail))
                <a class="ak-btn ak-ghost" href="mailto:{{ $akEmail }}">Email us</a>
            @else
                <a class="ak-btn ak-ghost" href="/contact">Contact us</a>
            @endif
        </div>
    </div>
</body>
</html>
