<?php

declare(strict_types=1);

namespace He4rt\Ai\Database\Factories;

use He4rt\Ai\Models\AiMessage;
use He4rt\Ai\Models\AiMessageFile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiMessageFile>
 */
final class AiMessageFileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'message_id' => AiMessage::factory(),
            'file_id' => fake()->uuid(),
            'name' => fake()->word(),
            'temporary_url' => fake()->url(),
            'mime_type' => fake()->mimeType(),
            'parsing_results' => fake()->text(),
        ];
    }
}
