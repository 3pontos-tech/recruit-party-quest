<?php

declare(strict_types=1);

namespace He4rt\Ai\Database\Factories;

use He4rt\Ai\Models\AiAssistant;
use He4rt\Ai\Models\AiThread;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiThread>
 */
final class AiThreadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'thread_id' => fake()->uuid(),
            'name' => fake()->word(),
            'assistant_id' => AiAssistant::factory(),
            'user_id' => User::factory(),
        ];
    }

    public function saved(): self
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->word(),
            'saved_at' => fake()->dateTime(),
        ]);
    }
}
