<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Page> */
class PageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->words(3, true);

        return [
            'title' => ucwords($title),
            'slug' => fake()->unique()->slug(),
            'blocks' => [
                [
                    'type' => 'richtext',
                    'data' => [
                        'html' => '<p>'.fake()->randomElement([
                            'We build resilient, production-ready software that ships fast and scales without surprises.',
                            'From discovery to deployment, our engineers turn ambitious ideas into dependable products.',
                            'Our team partners with you to design, develop, and maintain platforms your business can rely on.',
                        ]).'</p>',
                    ],
                ],
                [
                    'type' => 'richtext',
                    'data' => [
                        'html' => '<p>'.fake()->randomElement([
                            'Every engagement is backed by automated testing, clean architecture, and measurable outcomes.',
                            'We obsess over performance, security, and developer experience so your roadmap never stalls.',
                            'Expect transparent communication, rapid iteration, and code you can trust in production.',
                        ]).'</p>',
                    ],
                ],
            ],
            'seo_title' => ucwords($title).' | AKH Solutions',
            'seo_description' => fake()->randomElement([
                'AKH Solutions delivers high-performance software engineering, from architecture to deployment.',
                'Partner with AKH Solutions for scalable, secure, and reliable custom software development.',
                'Expert software consulting and delivery that helps your business move faster with confidence.',
            ]),
            'status' => 'published',
            'is_system' => false,
            'show_in_nav' => false,
            'sort_order' => fake()->numberBetween(0, 50),
            'published_at' => now(),
        ];
    }
}
