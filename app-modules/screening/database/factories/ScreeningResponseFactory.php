<?php

declare(strict_types=1);

namespace He4rt\Screening\Database\Factories;

use He4rt\Applications\Models\Application;
use He4rt\Screening\Models\ScreeningQuestion;
use He4rt\Screening\Models\ScreeningResponse;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ScreeningResponse> */
class ScreeningResponseFactory extends Factory
{
    protected $model = ScreeningResponse::class;

    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'question_id' => ScreeningQuestion::factory(),
            'response_value' => ['value' => fake()->word()],
            'is_knockout_fail' => false,
            'created_at' => now(),
        ];
    }

    public function yesNoResponse(bool $answer = true): static
    {
        return $this->state(fn (array $attributes) => [
            'response_value' => ['value' => $answer ? 'yes' : 'no'],
        ]);
    }

    public function textResponse(?string $text = null): static
    {
        return $this->state(fn (array $attributes) => [
            'response_value' => ['value' => $text ?? fake()->paragraph()],
        ]);
    }

    public function numberResponse(?int $number = null): static
    {
        return $this->state(fn (array $attributes) => [
            'response_value' => ['value' => $number ?? fake()->numberBetween(1, 100)],
        ]);
    }

    public function singleChoiceResponse(?string $choice = null): static
    {
        return $this->state(fn (array $attributes) => [
            'response_value' => ['value' => $choice ?? 'option_a'],
        ]);
    }

    public function multipleChoiceResponse(?array $choices = null): static
    {
        return $this->state(fn (array $attributes) => [
            'response_value' => ['value' => $choices ?? ['option_a', 'option_b']],
        ]);
    }

    public function knockoutFailed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_knockout_fail' => true,
        ]);
    }
}
