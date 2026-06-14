<?php

namespace App\Models;

use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'parent_id',
        'blocks',
        'seo_title',
        'seo_description',
        'og_image',
        'status',
        'is_system',
        'show_in_nav',
        'sort_order',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'blocks' => 'array',
            'is_system' => 'boolean',
            'show_in_nav' => 'boolean',
            'sort_order' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @param  Builder<Page>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published');
    }

    /**
     * @return BelongsTo<Page, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<Page, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('title');
    }

    /**
     * Ancestor chain from the root page down to (excluding) this page.
     *
     * @return Collection<int, Page>
     */
    public function ancestors(): Collection
    {
        $ancestors = collect();
        $current = $this->parent;
        $visited = [$this->id];

        // Walk up, guarding against accidental cycles in the data.
        while ($current !== null && ! in_array($current->id, $visited, true)) {
            $ancestors->prepend($current);
            $visited[] = $current->id;
            $current = $current->parent;
        }

        return $ancestors;
    }

    /**
     * Slugs whose content pages are served by dedicated top-level routes,
     * so they must never be reachable (or created) under /page/{slug}.
     *
     * @var list<string>
     */
    public const RESERVED_SLUGS = [
        'home', 'services', 'work', 'process', 'about', 'team',
        'insights', 'careers', 'contact', 'privacy', 'cookies', 'terms',
    ];

    public static function ownsTopLevelRoute(string $slug): bool
    {
        return in_array($slug, self::RESERVED_SLUGS, true);
    }

    /**
     * The public URL path for this page, including any ancestor slugs
     * (e.g. /page/parent/child). System pages keep their top-level routes.
     */
    public function publicPath(): string
    {
        if ($this->slug === 'home') {
            return '/';
        }

        if (self::ownsTopLevelRoute($this->slug)) {
            return "/{$this->slug}";
        }

        $segments = $this->ancestors()->pluck('slug')->push($this->slug)->implode('/');

        return "/page/{$segments}";
    }

    /**
     * Whether the given page is this page or one of its descendants -
     * used to prevent cycles when re-parenting.
     */
    public function isSelfOrDescendantOf(Page $page): bool
    {
        if ($this->id === $page->id) {
            return true;
        }

        return $this->ancestors()->contains(fn (Page $ancestor): bool => $ancestor->id === $page->id);
    }
}
