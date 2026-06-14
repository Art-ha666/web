<?php

namespace Database\Factories;

use App\Models\Theme;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Theme> */
class ThemeFactory extends Factory
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
            'name' => ucwords(fake()->words(2, true)),
            'description' => fake()->sentence(),
            'tokens' => [
                'bg' => '#07080d',
                'surface' => '#11131c',
                'primary' => '#5778f8',
                'accent' => '#22d3ee',
                'text' => '#e6e8f2',
            ],
            'hero_variant' => 'aurora',
            'layout_variant' => 'standard',
            'uses_three' => fake()->boolean(),
            'is_premium' => fake()->boolean(),
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
