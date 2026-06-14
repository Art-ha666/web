<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiProvider;
use App\Models\Article;
use App\Models\SiteSetting;
use App\Services\Ai\AiBlogWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AiWriterController extends Controller
{
    public function index(): Response
    {
        $settings = SiteSetting::current();

        return Inertia::render('admin/AiWriter', [
            'settings' => [
                'ai_blog_enabled' => (bool) $settings->ai_blog_enabled,
                'ai_blog_frequency' => $settings->ai_blog_frequency ?: 'twice_weekly',
                'ai_blog_per_run' => (int) ($settings->ai_blog_per_run ?: 1),
                'ai_blog_topics' => $settings->ai_blog_topics ?? '',
                'ai_blog_autopublish' => (bool) $settings->ai_blog_autopublish,
            ],
            'keyStatus' => [
                'openai' => filled(config('services.openai.key')),
                'gemini' => filled(config('services.gemini.key')),
            ],
            'modelOptions' => [
                'openai' => ['gpt-5.4-mini', 'gpt-5.4', 'gpt-5-mini', 'gpt-4o-mini', 'gpt-4o'],
                'gemini' => ['gemini-3.5-flash', 'gemini-3-flash-preview', 'gemini-2.5-flash', 'gemini-2.5-pro'],
            ],
            'aiProviders' => AiProvider::query()->orderBy('sort_order')->orderBy('id')->get()
                ->map(fn (AiProvider $p): array => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'provider' => $p->provider,
                    'model' => $p->model,
                    'is_active' => $p->is_active,
                    'has_own_key' => filled($p->api_key),
                    'usable' => $p->isUsable(),
                ])->all(),
            'recent' => Article::query()
                ->whereNotNull('generated_by')
                ->latest()
                ->limit(10)
                ->get(['id', 'title', 'slug', 'status', 'generated_by', 'created_at']),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ai_blog_enabled' => ['boolean'],
            'ai_blog_frequency' => ['required', Rule::in(['daily', 'twice_weekly', 'weekly', 'monthly'])],
            'ai_blog_per_run' => ['required', 'integer', 'min:1', 'max:5'],
            'ai_blog_topics' => ['nullable', 'string', 'max:2000'],
            'ai_blog_autopublish' => ['boolean'],
        ]);

        SiteSetting::current()->update([
            ...$validated,
            'ai_blog_enabled' => $request->boolean('ai_blog_enabled'),
            'ai_blog_autopublish' => $request->boolean('ai_blog_autopublish'),
        ]);

        return back()->with('success', 'AI writer settings saved.');
    }

    public function storeProvider(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'provider' => ['required', Rule::in(['openai', 'gemini'])],
            'model' => ['required', 'string', 'max:80'],
            'api_key' => ['nullable', 'string', 'max:300'],
        ]);

        AiProvider::create([
            'name' => $validated['name'],
            'provider' => $validated['provider'],
            'model' => $validated['model'],
            'api_key' => filled($validated['api_key'] ?? null) ? $validated['api_key'] : null,
            'is_active' => AiProvider::query()->count() === 0,
            'sort_order' => (int) AiProvider::query()->max('sort_order') + 1,
        ]);

        return back()->with('success', 'AI provider added.');
    }

    public function activateProvider(AiProvider $aiProvider): RedirectResponse
    {
        AiProvider::query()->whereKeyNot($aiProvider->getKey())->update(['is_active' => false]);
        $aiProvider->forceFill(['is_active' => true])->save();

        return back()->with('success', "“{$aiProvider->name}” is now the active AI.");
    }

    public function destroyProvider(AiProvider $aiProvider): RedirectResponse
    {
        $aiProvider->delete();

        return back()->with('success', 'AI provider removed.');
    }

    public function generate(Request $request, AiBlogWriter $writer): RedirectResponse
    {
        $count = (int) SiteSetting::current()->ai_blog_per_run ?: 1;

        try {
            $created = $writer->generate(max(1, min($count, 5)));
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'The AI provider could not be reached. Check the active AI / API key and try again.');
        }

        if ($created->isEmpty()) {
            return back()->with('message', 'No new posts - recent topics may already be covered, or the active AI returned nothing. Try again shortly.');
        }

        return back()->with('success', $created->count().' post(s) generated. Review them under Insights.');
    }
}
