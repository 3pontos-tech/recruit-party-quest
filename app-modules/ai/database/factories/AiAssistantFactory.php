<?php

declare(strict_types=1);

namespace He4rt\Ai\Database\Factories;

use He4rt\Ai\Enums\AiAssistantApplication;
use He4rt\Ai\Enums\AiModel;
use He4rt\Ai\Models\AiAssistant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiAssistant>
 */
final class AiAssistantFactory extends Factory
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
            'application' => AiAssistantApplication::PersonalAssistant,
            'model' => fake()->randomElement(
                array_filter(
                    AiModel::cases(),
                    fn (AiModel $case) => ! in_array($case, [AiModel::JinaDeepSearchV1, AiModel::LlamaParse]),
                )
            ),
        ];
    }
}
