<?php

namespace App\Models;

use Database\Factories\TestimonialFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Testimonial extends Model
{
    /** @use HasFactory<TestimonialFactory> */
    use HasFactory;

    protected $fillable = [
        'quote',
        'author_name',
        'author_role',
        'company_name',
        'company_logo',
        'avatar',
        'project_id',
        'rating',
        'featured',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @param  Builder<Testimonial>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<Testimonial>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}
