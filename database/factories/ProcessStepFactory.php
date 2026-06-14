<?php

namespace Database\Factories;

use App\Models\ProcessStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ProcessStep> */
class ProcessStepFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => '01',
            'title' => 'Discovery & Strategy',
            'description' => 'We map your goals, audit existing systems, and define a clear technical roadmap before a single line of code is written.',
            'deliverable_tag' => 'Roadmap',
            'icon' => 'compass',
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 20),
        ];
    }
}
