<?php

declare(strict_types=1);

namespace He4rt\Ai\Database\Factories;

use He4rt\Ai\Models\AiThreadFolder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiThreadFolder>
 */
final class AiThreadFolderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
        ];
    }
}
