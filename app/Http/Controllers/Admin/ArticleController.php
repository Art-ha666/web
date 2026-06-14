<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\TeamMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/articles/Index', [
            'articles' => Article::query()->latest('published_at')->get(['id', 'title', 'slug', 'status', 'featured', 'published_at']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/articles/Edit', [
            'article' => null,
            'authors' => TeamMember::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Article::create($this->validateData($request));

        return redirect()->route('admin.articles.index')->with('success', 'Article created.');
    }

    public function edit(Article $article): Response
    {
        return Inertia::render('admin/articles/Edit', [
            'article' => $article,
            'authors' => TeamMember::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        $article->update($this->validateData($request, $article));

        return redirect()->route('admin.articles.index')->with('success', 'Article saved.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        $article->delete();

        return redirect()->route('admin.articles.index')->with('success', 'Article deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateData(Request $request, ?Article $article = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:160', 'alpha_dash', Rule::unique('articles', 'slug')->ignore($article?->id)],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'string', 'max:255'],
            'author_id' => ['nullable', 'integer', Rule::exists('team_members', 'id')],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['nullable', 'string', 'max:60'],
            'reading_time' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'featured' => ['boolean'],
            'published_at' => ['nullable', 'date'],
            'seo_title' => ['nullable', 'string', 'max:160'],
            'seo_description' => ['nullable', 'string', 'max:300'],
        ]);

        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['title']);
        $data['tags'] = array_values(array_filter($data['tags'] ?? []));
        $data['featured'] = $request->boolean('featured');

        return $data;
    }
}
