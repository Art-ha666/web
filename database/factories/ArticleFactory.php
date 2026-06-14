<?php

namespace Database\Factories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence();

        return [
            'title' => $title,
            'slug' => fake()->unique()->slug(),
            'excerpt' => fake()->sentence(),
            'body' => '<p>'.implode('</p><p>', fake()->paragraphs(4)).'</p>',
            'cover_image' => null,
            'author_id' => null,
            'tags' => fake()->randomElements(
                ['engineering', 'product', 'devops', 'security', 'cloud', 'ai', 'architecture', 'leadership'],
                fake()->numberBetween(2, 3)
            ),
            'reading_time' => fake()->numberBetween(3, 12),
            'status' => 'published',
            'featured' => fake()->boolean(),
            'published_at' => now()->subDays(fake()->numberBetween(1, 365)),
            'seo_title' => $title,
            'seo_description' => fake()->sentence(),
        ];
    }
}
