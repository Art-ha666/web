<?php

namespace App\Models;

use Database\Factories\ThemeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Theme extends Model
{
    /** @use HasFactory<ThemeFactory> */
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'description',
        'tokens',
        'hero_variant',
        'layout_variant',
        'preview_image',
        'uses_three',
        'is_premium',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'tokens' => 'array',
            'uses_three' => 'boolean',
            'is_premium' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return HasMany<SiteSetting, $this>
     */
    public function siteSettings(): HasMany
    {
        return $this->hasMany(SiteSetting::class, 'active_theme_id');
    }
}
