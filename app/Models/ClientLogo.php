<?php

namespace App\Models;

use Database\Factories\ClientLogoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientLogo extends Model
{
    /** @use HasFactory<ClientLogoFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'logo_path',
        'logo_svg',
        'url',
        'type',
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
     * @param  Builder<ClientLogo>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<ClientLogo>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}
