<?php

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Lead> */
class LeadFactory extends Factory
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
            'business_email' => fake()->safeEmail(),
            'company' => fake()->company(),
            'phone' => fake()->phoneNumber(),
            'budget_range' => '$25k-$50k',
            'service_interest' => 'Custom Software',
            'message' => fake()->paragraph(),
            'source_page' => '/contact',
            'consent_marketing' => fake()->boolean(),
            'consent_data_processing' => true,
            'status' => 'new',
            'admin_notes' => null,
            'ip_address' => fake()->ipv4(),
            'user_agent' => 'Mozilla/5.0',
        ];
    }
}
