<?php

declare(strict_types=1);

namespace He4rt\Term\Database\Factories;

use He4rt\Term\Models\Term;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Term> */
class TermFactory extends Factory
{
    protected $model = Term::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->sentence(),
            'content' => [
                [
                    'id' => 'introduction',
                    'title' => 'Introduction',
                    'body' => '<p>'.fake()->paragraphs(2, true).'</p>',
                    'show_in_sidebar' => true,
                ],
                [
                    'id' => 'usage',
                    'title' => 'Usage',
                    'body' => '<p>'.fake()->paragraphs(3, true).'</p>',
                    'show_in_sidebar' => true,
                ],
            ],
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
