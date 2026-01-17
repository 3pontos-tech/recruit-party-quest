<?php

declare(strict_types=1);

use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Screening\Actions\SyncScreeningQuestionsAction;
use He4rt\Screening\DTOs\ScreeningQuestionDTO;
use He4rt\Screening\Enums\QuestionTypeEnum;
use He4rt\Screening\Models\ScreeningQuestion;

it('can create new questions', function (): void {
    $requisition = JobRequisition::factory()->create();

    $dtos = [
        new ScreeningQuestionDTO(
            requisitionId: $requisition->id,
            teamId: $requisition->team_id,
            questionText: 'Question 1',
            questionType: QuestionTypeEnum::YesNo,
            displayOrder: 1,
        ),
        new ScreeningQuestionDTO(
            requisitionId: $requisition->id,
            teamId: $requisition->team_id,
            questionText: 'Question 2',
            questionType: QuestionTypeEnum::Text,
            displayOrder: 2,
        ),
    ];

    $action = new SyncScreeningQuestionsAction();
    $action->handle($requisition, $dtos);

    expect($requisition->screeningQuestions()->count())->toBe(2)
        ->and($requisition->screeningQuestions()->orderBy('display_order')->first()->question_text)->toBe('Question 1');
});

it('can update existing questions', function (): void {
    $requisition = JobRequisition::factory()->create();
    $question = ScreeningQuestion::factory()->create([
        'requisition_id' => $requisition->id,
        'team_id' => $requisition->team_id,
        'question_text' => 'Original question',
        'question_type' => QuestionTypeEnum::Text,
        'display_order' => 1,
    ]);

    $dtos = [
        new ScreeningQuestionDTO(
            requisitionId: $requisition->id,
            teamId: $requisition->team_id,
            questionText: 'Updated question',
            questionType: QuestionTypeEnum::Number,
            displayOrder: 1,
            id: $question->id,
        ),
    ];

    $action = new SyncScreeningQuestionsAction();
    $action->handle($requisition, $dtos);

    $question->refresh();

    expect($requisition->screeningQuestions()->count())->toBe(1)
        ->and($question->question_text)->toBe('Updated question')
        ->and($question->question_type)->toBe(QuestionTypeEnum::Number);
});

it('can delete removed questions', function (): void {
    $requisition = JobRequisition::factory()->create();
    $questionToKeep = ScreeningQuestion::factory()->create([
        'requisition_id' => $requisition->id,
        'team_id' => $requisition->team_id,
        'display_order' => 1,
    ]);
    $questionToDelete = ScreeningQuestion::factory()->create([
        'requisition_id' => $requisition->id,
        'team_id' => $requisition->team_id,
        'display_order' => 2,
    ]);

    // Only include the question to keep in the DTOs
    $dtos = [
        new ScreeningQuestionDTO(
            requisitionId: $requisition->id,
            teamId: $requisition->team_id,
            questionText: $questionToKeep->question_text,
            questionType: $questionToKeep->question_type,
            displayOrder: 1,
            id: $questionToKeep->id,
        ),
    ];

    $action = new SyncScreeningQuestionsAction();
    $action->handle($requisition, $dtos);

    expect($requisition->screeningQuestions()->count())->toBe(1)
        ->and(ScreeningQuestion::query()->find($questionToDelete->id))->toBeNull()
        ->and(ScreeningQuestion::query()->find($questionToKeep->id))->not->toBeNull();
});

it('can handle mixed create, update, and delete operations', function (): void {
    $requisition = JobRequisition::factory()->create();
    $existingQuestion = ScreeningQuestion::factory()->create([
        'requisition_id' => $requisition->id,
        'team_id' => $requisition->team_id,
        'question_text' => 'Existing question',
        'display_order' => 1,
    ]);
    $questionToDelete = ScreeningQuestion::factory()->create([
        'requisition_id' => $requisition->id,
        'team_id' => $requisition->team_id,
        'display_order' => 2,
    ]);

    $dtos = [
        // Update existing
        new ScreeningQuestionDTO(
            requisitionId: $requisition->id,
            teamId: $requisition->team_id,
            questionText: 'Updated existing',
            questionType: $existingQuestion->question_type,
            displayOrder: 1,
            id: $existingQuestion->id,
        ),
        // Create new
        new ScreeningQuestionDTO(
            requisitionId: $requisition->id,
            teamId: $requisition->team_id,
            questionText: 'Brand new question',
            questionType: QuestionTypeEnum::SingleChoice,
            displayOrder: 2,
            choices: [
                ['value' => 'a', 'label' => 'Option A'],
                ['value' => 'b', 'label' => 'Option B'],
            ],
        ),
        // questionToDelete is not included, so it should be deleted
    ];

    $action = new SyncScreeningQuestionsAction();
    $action->handle($requisition, $dtos);

    $questions = $requisition->screeningQuestions()->orderBy('display_order')->get();

    expect($questions)->toHaveCount(2)
        ->and($questions[0]->question_text)->toBe('Updated existing')
        ->and($questions[1]->question_text)->toBe('Brand new question')
        ->and($questions[1]->choices)->toHaveCount(2)
        ->and(ScreeningQuestion::query()->find($questionToDelete->id))->toBeNull();
});

it('deletes all questions when empty array is passed', function (): void {
    $requisition = JobRequisition::factory()->create();
    ScreeningQuestion::factory()->count(3)->create([
        'requisition_id' => $requisition->id,
        'team_id' => $requisition->team_id,
    ]);

    expect($requisition->screeningQuestions()->count())->toBe(3);

    $action = new SyncScreeningQuestionsAction();
    $action->handle($requisition, []);

    expect($requisition->screeningQuestions()->count())->toBe(0);
});
