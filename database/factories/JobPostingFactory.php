<?php

namespace Database\Factories;

use App\Models\JobPosting;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<JobPosting> */
class JobPostingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => 'Senior Backend Engineer',
            'slug' => fake()->unique()->slug(),
            'department' => 'Engineering',
            'location' => 'Remote',
            'employment_type' => 'Full-time',
            'seniority' => 'Senior',
            'summary' => 'Help us ship resilient, production-grade systems that power our clients\' most ambitious products.',
            'description' => '<p>We build software that matters. As a Senior Backend Engineer you will own services end to end, partner directly with product and design, and set the technical bar for the team.</p><p>You will architect APIs, harden data pipelines, and mentor the engineers around you while we keep our delivery velocity high and our codebase clean.</p>',
            'responsibilities' => [
                'Design, build, and operate scalable backend services that our clients depend on every day.',
                'Own features from technical discovery through deployment, monitoring, and iteration.',
                'Champion clean architecture, thorough testing, and pragmatic code review across the team.',
                'Mentor engineers and raise the engineering standard through pairing and thoughtful feedback.',
            ],
            'requirements' => [
                '5+ years building and shipping production backend systems at scale.',
                'Deep expertise in PHP, Laravel, and modern API design patterns.',
                'Strong command of relational databases, query optimization, and data modeling.',
                'A track record of leading projects and delivering measurable business impact.',
            ],
            'tech_stack' => ['Laravel', 'PHP', 'PostgreSQL', 'Redis'],
            'salary_range' => '$90k-$140k',
            'is_open' => true,
            'apply_url' => null,
            'posted_at' => now(),
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
