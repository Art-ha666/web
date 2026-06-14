<?php

namespace App\Models;

use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'client_name',
        'client_type',
        'industry',
        'year',
        'category_tags',
        'headline_result',
        'summary',
        'challenge',
        'approach',
        'architecture_notes',
        'results',
        'cover_image',
        'gallery',
        'video_url',
        'tech_stack',
        'related_service_id',
        'featured',
        'is_published',
        'sort_order',
        'seo_title',
        'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'category_tags' => 'array',
            'results' => 'array',
            'gallery' => 'array',
            'tech_stack' => 'array',
            'featured' => 'boolean',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'related_service_id');
    }

    /**
     * @return HasMany<Testimonial, $this>
     */
    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }

    /**
     * @param  Builder<Project>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    /**
     * @param  Builder<Project>  $query
     */
    public function scopeFeatured(Builder $query): void
    {
        $query->where('featured', true);
    }

    /**
     * @param  Builder<Project>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderByDesc('id');
    }
}
