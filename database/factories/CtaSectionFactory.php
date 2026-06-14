<?php

namespace Database\Factories;

use App\Models\CtaSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CtaSection> */
class CtaSectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(),
            'eyebrow' => 'Ready?',
            'headline' => 'Ship the product your roadmap promised.',
            'body' => 'We pair senior engineers with sharp product thinking to turn your toughest ideas into software that performs in production.',
            'primary_cta_label' => 'Book a discovery call',
            'primary_cta_url' => '/contact',
            'secondary_cta_label' => 'Start a brief',
            'secondary_cta_url' => '/contact',
            'microcopy' => 'We reply within a day.',
            'gradient_variant' => 'brand',
            'is_active' => true,
        ];
    }
}
