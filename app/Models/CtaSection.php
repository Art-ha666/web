<?php

namespace App\Models;

use Database\Factories\CtaSectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CtaSection extends Model
{
    /** @use HasFactory<CtaSectionFactory> */
    use HasFactory;

    protected $fillable = [
        'key',
        'eyebrow',
        'headline',
        'body',
        'primary_cta_label',
        'primary_cta_url',
        'secondary_cta_label',
        'secondary_cta_url',
        'microcopy',
        'gradient_variant',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public static function forKey(string $key): ?self
    {
        return static::query()->where('key', $key)->where('is_active', true)->first();
    }
}
