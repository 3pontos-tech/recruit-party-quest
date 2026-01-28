<?php

declare(strict_types=1);

namespace He4rt\Links\Database\Factories;

use He4rt\Links\Link;
use He4rt\Links\LinkTypeEnum;
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
            'name' => fake()->sentence(3),
            'slug' => fake()->slug(),
            'url' => fake()->url(),
            'icon' => null,
            'type' => fake()->randomElement(LinkTypeEnum::cases()),
            'order_column' => fake()->numberBetween(1, 100),
        ];
    }
}
