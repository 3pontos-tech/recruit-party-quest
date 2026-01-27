<?php

declare(strict_types=1);

namespace He4rt\Links\Database\Factories;

use He4rt\Links\Link;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Link>
 */
class LinkFactory extends Factory
{
    protected $model = Link::class;

    public function definition(): array
    {
        return [
            'name' => [
                'en' => fake()->sentence(3),
            ],
            'slug' => [
                'en' => fake()->slug(),
            ],
            'url' => [
                'en' => fake()->url(),
            ],
            'icon' => null,
            'type' => fake()->randomElement(['primary', 'secondary', null]),
            'order_column' => fake()->numberBetween(1, 100),
        ];
    }
}
