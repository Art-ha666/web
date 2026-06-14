<?php

namespace App\Models;

use Database\Factories\ProcessStepFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessStep extends Model
{
    /** @use HasFactory<ProcessStepFactory> */
    use HasFactory;

    protected $fillable = [
        'number',
        'title',
        'description',
        'deliverable_tag',
        'icon',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @param  Builder<ProcessStep>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<ProcessStep>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}
