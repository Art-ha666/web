<?php

namespace Database\Seeders;

use App\Models\AiProvider;
use App\Models\CtaSection;
use App\Models\NavItem;
use App\Models\SiteSetting;
use App\Models\Theme;
use Illuminate\Database\Seeder;

class ThemeSeeder extends Seeder
{
    public function run(): void
    {
        $sort = 0;

        foreach (config('themes') as $key => $preset) {
            Theme::updateOrCreate(
                ['key' => $key],
                [
                    'name' => $preset['name'],
                    'description' => $preset['description'] ?? null,
                    'tokens' => $preset['tokens'],
                    'hero_variant' => $preset['hero_variant'] ?? 'aurora',
                    'layout_variant' => $preset['layout_variant'] ?? 'standard',
                    'uses_three' => $preset['uses_three'] ?? false,
                    'is_premium' => $preset['is_premium'] ?? false,
                    'sort_order' => $sort++,
                ],
            );
        }

        $aurora = Theme::where('key', 'aurora')->first();

        /*
        | firstOrCreate, NOT updateOrCreate: reseeding must never clobber the
        | admin's live settings (a previous version of this seeder silently
        | reverted the contact email/phone on every db:seed).
        */
        SiteSetting::firstOrCreate(
            ['id' => 1],
            [
                'site_name' => 'AKH Solutions',
                'tagline' => 'Always ahead',
                'primary_email' => 'info@akhsolutions.net',
                // No fabricated contact channels: set real ones in Admin → Settings.
                'phone' => null,
                'whatsapp' => null,
                'telegram' => null,
                'address' => 'Remote-first software agency · worldwide',
                // No fabricated social profiles: add real ones in Admin → Settings.
                'socials' => [],
                'locations' => ['Remote-first', 'Worldwide'],
                'active_theme_id' => $aurora?->id,
                'nav_cta_label' => 'Book a discovery call',
                'nav_cta_url' => '/contact',
                'footer_blurb' => 'AKH Solutions is a software, AI, and IT engineering agency. We take on the unusual, the complex, and the mission-critical - and ship systems that work, from embedded devices to cloud platforms.',
                'default_meta_title' => 'AKH Solutions - Software, AI & IT engineering agency',
                'default_meta_description' => 'A full-spectrum engineering agency: web, mobile, AI, embedded, desktop, robotics, IoT, networking, and cloud. One team from first idea to production.',
                'announcement_text' => 'Now booking new engineering projects for Q3 2026.',
                'announcement_active' => true,
                // AI blog writer defaults: autonomous, twice a week, auto-published.
                // Admin can change or disable any of this in Admin → AI Writer.
                'ai_blog_enabled' => true,
                'ai_provider' => 'openai',
                'ai_blog_frequency' => 'twice_weekly',
                'ai_blog_per_run' => 1,
                'ai_blog_autopublish' => true,
                'ai_openai_model' => 'gpt-5.4-mini',
                'ai_gemini_model' => 'gemini-3.5-flash',
            ],
        );

        $this->seedNav();
        $this->seedCtas();
        $this->seedAiProviders();
    }

    protected function seedAiProviders(): void
    {
        // API keys come from the environment (encrypted if entered via admin).
        // These default entries use null keys → the env key is used.
        $providers = [
            ['name' => 'OpenAI', 'provider' => 'openai', 'model' => 'gpt-5.4-mini', 'is_active' => true, 'sort_order' => 0],
            ['name' => 'Gemini', 'provider' => 'gemini', 'model' => 'gemini-3.5-flash', 'is_active' => false, 'sort_order' => 1],
        ];

        foreach ($providers as $p) {
            AiProvider::updateOrCreate(
                ['name' => $p['name'], 'provider' => $p['provider']],
                ['model' => $p['model'], 'api_key' => null, 'is_active' => $p['is_active'], 'sort_order' => $p['sort_order']],
            );
        }
    }

    protected function seedNav(): void
    {
        $header = [
            ['label' => 'Services', 'url' => '/services'],
            ['label' => 'Work', 'url' => '/work'],
            ['label' => 'Process', 'url' => '/process'],
            ['label' => 'About', 'url' => '/about'],
            ['label' => 'Insights', 'url' => '/insights'],
        ];

        foreach ($header as $i => $item) {
            NavItem::updateOrCreate(
                ['location' => 'header', 'label' => $item['label']],
                ['url' => $item['url'], 'sort_order' => $i, 'is_active' => true],
            );
        }

        $footerGroups = [
            'Services' => [
                ['Custom Software', '/services/custom-software'],
                ['Web & Mobile Apps', '/services/web-mobile-apps'],
                ['AI & Data', '/services/ai-and-data'],
                ['Embedded, IoT & Robotics', '/services/embedded-iot-robotics'],
                ['Cloud & Networking', '/services/platform-devops'],
                ['Team Extension', '/services/team-extension'],
            ],
            'Company' => [
                ['About', '/about'],
                ['Team', '/team'],
                ['Careers', '/careers'],
                ['Insights', '/insights'],
                ['Contact', '/contact'],
            ],
            'Work' => [
                ['Case Studies', '/work'],
                ['Process', '/process'],
            ],
            'Legal' => [
                ['Privacy', '/privacy'],
                ['Cookies', '/cookies'],
                ['Terms', '/terms'],
            ],
        ];

        $order = 0;
        foreach ($footerGroups as $group => $links) {
            foreach ($links as $i => [$label, $url]) {
                NavItem::updateOrCreate(
                    ['location' => 'footer', 'mega_group' => $group, 'label' => $label],
                    ['url' => $url, 'sort_order' => $order++, 'is_active' => true],
                );
            }
        }

        // Remove footer links this seeder used to create under old labels.
        foreach ($footerGroups as $group => $links) {
            NavItem::query()
                ->where('location', 'footer')
                ->where('mega_group', $group)
                ->whereNotIn('label', array_column($links, 0))
                ->delete();
        }
    }

    protected function seedCtas(): void
    {
        CtaSection::updateOrCreate(['key' => 'closing'], [
            'eyebrow' => 'Start the conversation',
            'headline' => "Tell us what you're building.",
            'body' => 'Book a 30-minute discovery call - no pitch deck, just engineers who have shipped what you need.',
            'primary_cta_label' => 'Book a discovery call',
            'primary_cta_url' => '/contact',
            'secondary_cta_label' => 'Start a project brief',
            'secondary_cta_url' => '/contact',
            'microcopy' => 'We reply to every serious inquiry within one business day.',
            'gradient_variant' => 'brand',
            'is_active' => true,
        ]);

        CtaSection::updateOrCreate(['key' => 'manifesto'], [
            'eyebrow' => 'Our point of view',
            'headline' => 'Most teams react. We get ahead.',
            'body' => 'Falling behind is a series of small, late decisions. We make the early ones - the architecture, the trade-offs, the boring reliability work - so your product is ready before the market asks for it.',
            'primary_cta_label' => 'Read our engineering principles',
            'primary_cta_url' => '/about',
            'gradient_variant' => 'violet',
            'is_active' => true,
        ]);
    }
}
