<?php

namespace App\Models;

use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'icon',
        'tab_label',
        'short_blurb',
        'intro',
        'value_metric',
        'benefit_bullets',
        'detail_body',
        'gradient',
        'demo_media',
        'tech_stack',
        'featured',
        'is_active',
        'sort_order',
        'seo_title',
        'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'benefit_bullets' => 'array',
            'gradient' => 'array',
            'tech_stack' => 'array',
            'featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return HasMany<Project, $this>
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'related_service_id');
    }

    /**
     * @param  Builder<Service>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<Service>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}
