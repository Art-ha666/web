<?php

namespace App\Models;

use Database\Factories\NavItemFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NavItem extends Model
{
    /** @use HasFactory<NavItemFactory> */
    use HasFactory;

    protected $fillable = [
        'label',
        'url',
        'parent_id',
        'location',
        'mega_group',
        'description',
        'icon',
        'is_cta',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_cta' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<NavItem, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(NavItem::class, 'parent_id');
    }

    /**
     * @return HasMany<NavItem, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(NavItem::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * @param  Builder<NavItem>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
