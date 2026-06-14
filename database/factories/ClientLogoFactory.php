<?php

namespace Database\Factories;

use App\Models\ClientLogo;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ClientLogo> */
class ClientLogoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'logo_path' => null,
            'logo_svg' => null,
            'url' => fake()->url(),
            'type' => 'client',
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
