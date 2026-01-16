<?php

declare(strict_types=1);

namespace He4rt\Ai\Database\Factories;

use He4rt\Ai\Models\AiMessage;
use He4rt\Ai\Models\AiThread;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiMessage>
 */
final class AiMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'message_id' => fake()->uuid(),
            'content' => fake()->sentence(),
            'context' => fake()->word(),
            'request' => fake()->word(),
            'thread_id' => AiThread::factory(),
            'user_id' => User::factory(),
        ];
    }
}
