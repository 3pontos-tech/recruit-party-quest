<?php

declare(strict_types=1);

use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Screening\Enums\QuestionTypeEnum;
use He4rt\Screening\Models\ScreeningQuestion;

it('can create a screening question', function (): void {
    $question = ScreeningQuestion::factory()->create();

    expect($question)->toBeInstanceOf(ScreeningQuestion::class)
        ->and($question->id)->not->toBeNull()
        ->and($question->question_text)->not->toBeNull()
        ->and($question->question_type)->toBeInstanceOf(QuestionTypeEnum::class);
});

it('can create a yes/no question', function (): void {
    $question = ScreeningQuestion::factory()->yesNo()->create();

    expect($question->question_type)->toBe(QuestionTypeEnum::YesNo);
});

it('can create a single choice question with choices', function (): void {
    $question = ScreeningQuestion::factory()->singleChoice()->create();

    expect($question->question_type)->toBe(QuestionTypeEnum::SingleChoice)
        ->and($question->choices)->toBeArray()
        ->and($question->choices)->toHaveCount(3);
});

it('can create a multiple choice question with choices', function (): void {
    $question = ScreeningQuestion::factory()->multipleChoice()->create();

    expect($question->question_type)->toBe(QuestionTypeEnum::MultipleChoice)
        ->and($question->choices)->toBeArray()
        ->and($question->choices)->toHaveCount(4);
});

it('can create a knockout question', function (): void {
    $question = ScreeningQuestion::factory()->knockout(['expected' => 'yes'])->create();

    expect($question->is_knockout)->toBeTrue()
        ->and($question->knockout_criteria)->toBe(['expected' => 'yes']);
});

it('can create a required question', function (): void {
    $question = ScreeningQuestion::factory()->required()->create();

    expect($question->is_required)->toBeTrue();
});

it('belongs to a requisition', function (): void {
    $requisition = JobRequisition::factory()->create();
    $question = ScreeningQuestion::factory()->create(['requisition_id' => $requisition->id]);

    expect($question->requisition)->toBeInstanceOf(JobRequisition::class)
        ->and($question->requisition->id)->toBe($requisition->id);
});

it('can be accessed from requisition', function (): void {
    $requisition = JobRequisition::factory()->create();
    $questions = ScreeningQuestion::factory()->count(3)->create([
        'requisition_id' => $requisition->id,
        'display_order' => fn () => fake()->unique()->numberBetween(1, 100),
    ]);

    expect($requisition->screeningQuestions)->toHaveCount(3);
});
