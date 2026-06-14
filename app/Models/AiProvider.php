<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiProvider extends Model
{
    protected $fillable = [
        'name',
        'provider',
        'model',
        'api_key',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            // API keys are stored encrypted at rest - never plaintext in the DB.
            'api_key' => 'encrypted',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Whether this provider can actually run - it has a key (its own, or the
     * matching env fallback).
     */
    public function isUsable(): bool
    {
        return filled($this->api_key) || filled(config("services.{$this->provider}.key"));
    }
}
