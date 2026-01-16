<?php

declare(strict_types=1);

namespace He4rt\Ai\Database\Factories;

use He4rt\Ai\Models\Prompt;
use He4rt\Ai\Models\PromptType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Prompt>
 */
final class PromptFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => str(fake()->unique()->words(asText: true))->ucfirst()->toString(),
            'description' => fake()->optional()->sentences(asText: true),
            'prompt' => fake()->sentences(asText: true),
            'type_id' => PromptType::query()->inRandomOrder()->first() ?? PromptType::factory()->create(),
        ];
    }
}
