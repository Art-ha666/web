<?php

namespace Database\Factories;

use App\Models\NavItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<NavItem> */
class NavItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'label' => fake()->word(),
            'url' => '/services',
            'parent_id' => null,
            'location' => 'header',
            'mega_group' => null,
            'description' => null,
            'icon' => null,
            'is_cta' => false,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 99),
        ];
    }
}
