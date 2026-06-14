<?php

namespace Database\Factories;

use App\Models\Stat;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Stat> */
class StatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'value' => '120',
            'prefix' => null,
            'suffix' => '+',
            'label' => 'products shipped',
            'group' => 'band',
            'accent_color' => null,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 50),
        ];
    }
}
