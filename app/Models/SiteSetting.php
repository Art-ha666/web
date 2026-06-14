<?php

namespace App\Models;

use Database\Factories\SiteSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteSetting extends Model
{
    /** @use HasFactory<SiteSettingFactory> */
    use HasFactory;

    protected $fillable = [
        'site_name',
        'tagline',
        'logo_path',
        'favicon_path',
        'primary_email',
        'phone',
        'whatsapp',
        'telegram',
        'address',
        'socials',
        'locations',
        'active_theme_id',
        'nav_cta_label',
        'nav_cta_url',
        'footer_blurb',
        'default_meta_title',
        'default_meta_description',
        'og_image',
        'announcement_text',
        'announcement_active',
        'custom_tokens',
        'ga_measurement_id',
        'head_scripts',
        'newsletter_heading',
        'newsletter_placeholder',
        'newsletter_success',
        'cookie_banner_text',
        'cookie_accept_label',
        'cookie_decline_label',
        'ai_blog_enabled',
        'ai_provider',
        'ai_blog_frequency',
        'ai_blog_per_run',
        'ai_blog_topics',
        'ai_blog_autopublish',
        'ai_openai_model',
        'ai_gemini_model',
    ];

    protected function casts(): array
    {
        return [
            'socials' => 'array',
            'locations' => 'array',
            'custom_tokens' => 'array',
            'announcement_active' => 'boolean',
            'ai_blog_enabled' => 'boolean',
            'ai_blog_autopublish' => 'boolean',
            'ai_blog_per_run' => 'integer',
        ];
    }

    /**
     * Resolve the single site settings row, creating defaults if needed.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }

    /**
     * @return BelongsTo<Theme, $this>
     */
    public function activeTheme(): BelongsTo
    {
        return $this->belongsTo(Theme::class, 'active_theme_id');
    }
}
