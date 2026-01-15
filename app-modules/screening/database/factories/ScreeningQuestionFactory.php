<?php

declare(strict_types=1);

namespace He4rt\Screening\Database\Factories;

use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Screening\Enums\QuestionTypeEnum;
use He4rt\Screening\Models\ScreeningQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ScreeningQuestion> */
class ScreeningQuestionFactory extends Factory
{
    protected $model = ScreeningQuestion::class;

    public function definition(): array
    {
        return [
            'requisition_id' => JobRequisition::factory(),
            'question_text' => fake()->sentence().'?',
            'question_type' => fake()->randomElement(QuestionTypeEnum::cases()),
            'choices' => null,
            'is_required' => fake()->boolean(70),
            'is_knockout' => fake()->boolean(30),
            'knockout_criteria' => null,
            'display_order' => fake()->numberBetween(1, 100),
        ];
    }

    public function yesNo(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => QuestionTypeEnum::YesNo,
            'choices' => null,
        ]);
    }

    public function singleChoice(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => QuestionTypeEnum::SingleChoice,
            'choices' => [
                ['value' => 'option_a', 'label' => 'Option A'],
                ['value' => 'option_b', 'label' => 'Option B'],
                ['value' => 'option_c', 'label' => 'Option C'],
            ],
        ]);
    }

    public function multipleChoice(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => QuestionTypeEnum::MultipleChoice,
            'choices' => [
                ['value' => 'option_a', 'label' => 'Option A'],
                ['value' => 'option_b', 'label' => 'Option B'],
                ['value' => 'option_c', 'label' => 'Option C'],
                ['value' => 'option_d', 'label' => 'Option D'],
            ],
        ]);
    }

    public function text(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => QuestionTypeEnum::Text,
            'choices' => null,
        ]);
    }

    public function number(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => QuestionTypeEnum::Number,
            'choices' => null,
        ]);
    }

    public function fileUpload(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => QuestionTypeEnum::FileUpload,
            'choices' => null,
        ]);
    }

    public function required(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_required' => true,
        ]);
    }

    public function knockout(array $criteria = ['expected' => 'yes']): static
    {
        return $this->state(fn (array $attributes) => [
            'is_knockout' => true,
            'knockout_criteria' => $criteria,
        ]);
    }
}
