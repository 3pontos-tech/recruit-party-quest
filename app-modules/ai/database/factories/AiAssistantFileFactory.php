<?php

declare(strict_types=1);

namespace He4rt\Ai\Database\Factories;

use He4rt\Ai\Models\AiAssistantFile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiAssistantFile>
 */
final class AiAssistantFileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'parsing_results' => fake()->text(),
        ];
    }
}
