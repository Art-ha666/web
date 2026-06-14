<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);
        $title = ucwords($title);

        return [
            'title' => $title,
            'slug' => fake()->unique()->slug(),
            'icon' => 'code',
            'tab_label' => $title,
            'short_blurb' => fake()->sentence(),
            'intro' => fake()->paragraph(),
            'value_metric' => '40% faster delivery',
            'benefit_bullets' => [
                'Ship production-ready features in days, not months',
                'Battle-tested architecture that scales with your business',
                'Transparent communication at every sprint',
                'Long-term maintainability baked in from day one',
            ],
            'detail_body' => '<p>'.fake()->paragraph().'</p><p>'.fake()->paragraph().'</p>',
            'gradient' => [
                'from' => '#28baf3',
                'via' => '#5778f8',
                'to' => '#7e2cfd',
            ],
            'tech_stack' => ['Laravel', 'Vue', 'PostgreSQL', 'Redis'],
            'featured' => fake()->boolean(),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 100),
            'seo_title' => $title.' | Akh Solutions',
            'seo_description' => fake()->sentence(),
        ];
    }
}
