<?php

namespace App\Models;

use Database\Factories\JobPostingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPosting extends Model
{
    /** @use HasFactory<JobPostingFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'department',
        'location',
        'employment_type',
        'seniority',
        'summary',
        'description',
        'responsibilities',
        'requirements',
        'tech_stack',
        'salary_range',
        'is_open',
        'apply_url',
        'posted_at',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'responsibilities' => 'array',
            'requirements' => 'array',
            'tech_stack' => 'array',
            'is_open' => 'boolean',
            'posted_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @param  Builder<JobPosting>  $query
     */
    public function scopeOpen(Builder $query): void
    {
        $query->where('is_open', true);
    }
}
