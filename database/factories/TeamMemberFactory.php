<?php

namespace Database\Factories;

use App\Models\TeamMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TeamMember> */
class TeamMemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'slug' => fake()->unique()->slug(),
            'role' => 'Principal Engineer',
            'specialty' => 'Distributed systems',
            'bio' => fake()->paragraph(),
            'photo' => null,
            'years_experience' => fake()->numberBetween(5, 18),
            'linkedin' => fake()->url(),
            'github' => fake()->url(),
            'twitter' => null,
            'featured' => fake()->boolean(),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 50),
        ];
    }
}
