<?php

namespace App\Models;

use Database\Factories\TeamMemberFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeamMember extends Model
{
    /** @use HasFactory<TeamMemberFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'role',
        'specialty',
        'bio',
        'photo',
        'years_experience',
        'linkedin',
        'github',
        'twitter',
        'featured',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'years_experience' => 'integer',
            'featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return HasMany<Article, $this>
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'author_id');
    }

    /**
     * @param  Builder<TeamMember>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<TeamMember>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}
