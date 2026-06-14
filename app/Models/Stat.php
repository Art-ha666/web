<?php

namespace App\Models;

use Database\Factories\StatFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stat extends Model
{
    /** @use HasFactory<StatFactory> */
    use HasFactory;

    protected $fillable = [
        'value',
        'prefix',
        'suffix',
        'label',
        'group',
        'accent_color',
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
     * @param  Builder<Stat>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<Stat>  $query
     */
    public function scopeGroup(Builder $query, string $group): void
    {
        $query->where('group', $group);
    }

    /**
     * @param  Builder<Stat>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}
