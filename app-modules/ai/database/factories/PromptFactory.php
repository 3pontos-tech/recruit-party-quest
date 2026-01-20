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
     * Provide default attributes for a Prompt model factory.
     *
     * Returns an associative array with the following attributes:
     * - title: a unique set of words joined into a string with the first letter capitalized.
     * - description: optional sentences or null.
     * - prompt: one or more sentences.
     * - type_id: a PromptType factory instance for creating the related type.
     *
     * @return array<string,mixed> Associative array of default attribute values for a Prompt.
     */
    public function definition(): array
    {
        return [
            'title' => str(fake()->unique()->words(true))->ucfirst()->toString(),
            'description' => fake()->optional()->sentences(true),
            'prompt' => fake()->sentences(true),
            'type_id' => PromptType::factory(),
        ];
    }
}