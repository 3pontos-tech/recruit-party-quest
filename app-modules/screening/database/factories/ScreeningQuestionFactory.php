<?php

declare(strict_types=1);

namespace He4rt\Screening\Database\Factories;

use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Screening\Enums\QuestionTypeEnum;
use He4rt\Screening\Models\ScreeningQuestion;
use He4rt\Teams\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Relations\Relation;

/** @extends Factory<ScreeningQuestion> */
class ScreeningQuestionFactory extends Factory
{
    protected $model = ScreeningQuestion::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'screenable_type' => Relation::getMorphAlias(JobRequisition::class),
            'screenable_id' => JobRequisition::factory(),
            'question_text' => fake()->sentence().'?',
            'question_type' => fake()->randomElement(QuestionTypeEnum::cases()),
            'settings' => null,
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
            'settings' => [],
        ]);
    }

    public function singleChoice(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => QuestionTypeEnum::SingleChoice,
            'settings' => [
                'layout' => 'radio',
                'choices' => [
                    ['value' => 'option_a', 'label' => 'Option A'],
                    ['value' => 'option_b', 'label' => 'Option B'],
                    ['value' => 'option_c', 'label' => 'Option C'],
                ],
            ],
        ]);
    }

    public function multipleChoice(int $min = 0, ?int $max = null): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => QuestionTypeEnum::MultipleChoice,
            'settings' => [
                'min_selections' => $min,
                'max_selections' => $max,
                'choices' => [
                    ['value' => 'option_a', 'label' => 'Option A'],
                    ['value' => 'option_b', 'label' => 'Option B'],
                    ['value' => 'option_c', 'label' => 'Option C'],
                    ['value' => 'option_d', 'label' => 'Option D'],
                ],
            ],
        ]);
    }

    public function text(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => QuestionTypeEnum::Text,
            'settings' => [
                'max_length' => null,
                'multiline' => false,
                'placeholder' => null,
            ],
        ]);
    }

    public function number(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => QuestionTypeEnum::Number,
            'settings' => [
                'min' => null,
                'max' => null,
                'step' => null,
                'prefix' => null,
                'suffix' => null,
            ],
        ]);
    }

    public function fileUpload(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => QuestionTypeEnum::FileUpload,
            'settings' => [
                'max_size_kb' => 5120,
                'max_files' => 1,
                'allowed_extensions' => ['pdf', 'doc', 'docx'],
            ],
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
