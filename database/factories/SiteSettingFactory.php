<?php

namespace Database\Factories;

use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SiteSetting> */
class SiteSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_name' => 'AKH Solutions',
            'tagline' => 'Always ahead',
            'primary_email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'socials' => [
                'linkedin' => 'https://www.linkedin.com/company/'.fake()->unique()->slug(),
            ],
            'locations' => [
                fake()->city(),
                fake()->city(),
                fake()->city(),
            ],
            'nav_cta_label' => 'Book a call',
            'nav_cta_url' => '/contact',
            'footer_blurb' => 'We design, build, and ship resilient software that moves your business forward.',
            'default_meta_title' => 'AKH Solutions - Software Engineering & Product Delivery',
            'default_meta_description' => 'AKH Solutions partners with ambitious teams to architect, build, and scale modern software that ships fast and lasts.',
        ];
    }
}
