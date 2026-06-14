<?php

namespace Database\Factories;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Testimonial> */
class TestimonialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quote' => 'Partnering with this team transformed our delivery pipeline and cut our release cycle in half. They shipped production-grade code on time and treated our roadmap like their own.',
            'author_name' => fake()->name(),
            'author_role' => 'CTO',
            'company_name' => fake()->company(),
            'company_logo' => null,
            'avatar' => null,
            'project_id' => null,
            'rating' => 5,
            'featured' => fake()->boolean(),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
