<?php

namespace App\Models;

use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'body',
        'cover_image',
        'author_id',
        'tags',
        'reading_time',
        'status',
        'featured',
        'published_at',
        'seo_title',
        'seo_description',
        'generated_by',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'reading_time' => 'integer',
            'featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsTo<TeamMember, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class, 'author_id');
    }

    /**
     * @param  Builder<Article>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}
