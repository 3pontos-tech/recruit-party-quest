<?php

declare(strict_types=1);

namespace He4rt\Feedback\Database\Factories;

use He4rt\Applications\Models\Application;
use He4rt\Feedback\Enums\EvaluationRatingEnum;
use He4rt\Feedback\Models\Evaluation;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Teams\Team;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Evaluation> */
class EvaluationFactory extends Factory
{
    protected $model = Evaluation::class;

    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'team_id' => Team::factory(),
            'stage_id' => Stage::factory(),
            'evaluator_id' => User::factory(),
            'overall_rating' => fake()->randomElement(EvaluationRatingEnum::cases()),
            'recommendation' => fake()->paragraph(),
            'strengths' => fake()->paragraph(),
            'concerns' => fake()->optional()->paragraph(),
            'notes' => fake()->optional()->paragraph(),
            'criteria_scores' => [
                'communication' => fake()->numberBetween(1, 5),
                'technical_skills' => fake()->numberBetween(1, 5),
                'problem_solving' => fake()->numberBetween(1, 5),
            ],
            'submitted_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'submitted_at' => now(),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'submitted_at' => null,
        ]);
    }

    public function positive(): static
    {
        return $this->state(fn (array $attributes) => [
            'overall_rating' => fake()->randomElement([EvaluationRatingEnum::Yes, EvaluationRatingEnum::StrongYes]),
        ]);
    }

    public function negative(): static
    {
        return $this->state(fn (array $attributes) => [
            'overall_rating' => fake()->randomElement([EvaluationRatingEnum::No, EvaluationRatingEnum::StrongNo]),
        ]);
    }
}
