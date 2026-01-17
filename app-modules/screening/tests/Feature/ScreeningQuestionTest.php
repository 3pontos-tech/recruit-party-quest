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
        ->and($question->settings)->toBeObject()
        ->and($question->settings->choices)->toHaveCount(3);
});

it('can create a multiple choice question with choices', function (): void {
    $question = ScreeningQuestion::factory()->multipleChoice()->create();

    expect($question->question_type)->toBe(QuestionTypeEnum::MultipleChoice)
        ->and($question->settings)->toBeObject()
        ->and($question->settings->choices)->toHaveCount(4);
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
    $question = ScreeningQuestion::factory()
        ->create(['screenable_id' => $requisition->id]);

    expect($question->screenable)->toBeInstanceOf(JobRequisition::class)
        ->and($question->screenable_id)->toBe($requisition->id);
});

it('can be accessed from requisition', function (): void {
    $requisition = JobRequisition::factory()->create();
    $questions = ScreeningQuestion::factory()->count(3)->create([
        'screenable_id' => $requisition->id,
        'display_order' => fn () => fake()->unique()->numberBetween(1, 100),
    ]);

    expect($requisition->refresh()->screeningQuestions)->toHaveCount(3);
});
