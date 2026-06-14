<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Project> */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->catchPhrase();

        return [
            'title' => $title,
            'slug' => fake()->unique()->slug(),
            'client_name' => fake()->company(),
            'client_type' => 'Scale-up',
            'industry' => 'Fintech',
            'year' => '2025',
            'category_tags' => fake()->randomElements(
                ['Platform Engineering', 'Cloud Migration', 'Observability', 'DevOps', 'Data Platform', 'API Design'],
                fake()->numberBetween(2, 3)
            ),
            'headline_result' => '38% lower infra cost',
            'summary' => fake()->sentence(12),
            'challenge' => fake()->paragraph(5),
            'approach' => '<p>We embedded a small senior team alongside the client and shipped in tight, measurable increments. Discovery mapped the highest-leverage bottlenecks before we wrote a single line of production code.</p><p>From there we re-platformed the critical path, hardened the deployment pipeline, and instrumented everything so every decision was driven by data rather than guesswork.</p>',
            'architecture_notes' => '<ul><li>Stateless services behind an autoscaling load balancer for predictable, cost-efficient throughput.</li><li>Event-driven workflows backed by a durable message queue to decouple read and write paths.</li><li>Infrastructure as code with automated rollbacks and full observability across traces, metrics, and logs.</li></ul>',
            'results' => [
                ['metric' => '38%', 'label' => 'lower infrastructure cost'],
                ['metric' => '4.2x', 'label' => 'faster deploy cadence'],
                ['metric' => '99.98%', 'label' => 'platform uptime'],
            ],
            'cover_image' => null,
            'gallery' => [
                'https://placehold.co/1280x720/png',
                'https://placehold.co/1280x720/png',
            ],
            'video_url' => null,
            'tech_stack' => fake()->randomElements(
                ['Laravel', 'Vue', 'PostgreSQL', 'Redis', 'Terraform', 'Kubernetes', 'AWS', 'Inertia'],
                4
            ),
            'related_service_id' => null,
            'featured' => fake()->boolean(),
            'is_published' => true,
            'sort_order' => fake()->numberBetween(0, 100),
            'seo_title' => $title.' | Case Study',
            'seo_description' => fake()->sentence(16),
        ];
    }
}
