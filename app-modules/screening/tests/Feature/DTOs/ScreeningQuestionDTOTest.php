<?php

declare(strict_types=1);

use He4rt\Screening\DTOs\ScreeningQuestionDTO;
use He4rt\Screening\Enums\QuestionTypeEnum;

it('can create a DTO from constructor', function (): void {
    $dto = new ScreeningQuestionDTO(
        requisitionId: 'req-123',
        teamId: 'team-456',
        questionText: 'Are you authorized to work?',
        questionType: QuestionTypeEnum::YesNo,
        displayOrder: 1,
    );

    expect($dto->requisitionId)->toBe('req-123')
        ->and($dto->teamId)->toBe('team-456')
        ->and($dto->questionText)->toBe('Are you authorized to work?')
        ->and($dto->questionType)->toBe(QuestionTypeEnum::YesNo)
        ->and($dto->displayOrder)->toBe(1)
        ->and($dto->isRequired)->toBeTrue()
        ->and($dto->isKnockout)->toBeFalse()
        ->and($dto->id)->toBeNull();
});

it('can create a DTO from array', function (): void {
    $dto = ScreeningQuestionDTO::fromArray([
        'requisition_id' => 'req-123',
        'team_id' => 'team-456',
        'question_text' => 'Years of experience?',
        'question_type' => 'number',
        'display_order' => 2,
        'is_required' => true,
        'is_knockout' => true,
        'knockout_criteria' => ['minimum' => 3],
        'id' => 'question-789',
    ]);

    expect($dto->questionType)->toBe(QuestionTypeEnum::Number)
        ->and($dto->isKnockout)->toBeTrue()
        ->and($dto->knockoutCriteria)->toBe(['minimum' => 3])
        ->and($dto->id)->toBe('question-789');
});

it('can create a DTO from array with enum instance and settings', function (): void {
    $dto = ScreeningQuestionDTO::fromArray([
        'requisition_id' => 'req-123',
        'team_id' => 'team-456',
        'question_text' => 'Select your skills',
        'question_type' => QuestionTypeEnum::MultipleChoice,
        'display_order' => 3,
        'settings' => [
            'min_selections' => 1,
            'max_selections' => 3,
            'choices' => [
                ['value' => 'php', 'label' => 'PHP'],
                ['value' => 'js', 'label' => 'JavaScript'],
            ],
        ],
    ]);

    expect($dto->questionType)->toBe(QuestionTypeEnum::MultipleChoice)
        ->and($dto->settings)->toHaveKey('choices')
        ->and($dto->settings['choices'])->toHaveCount(2);
});

it('can create a collection from repeater data', function (): void {
    $repeaterData = [
        [
            'question_text' => 'Question 1',
            'question_type' => 'yes_no',
            'is_required' => true,
        ],
        [
            'question_text' => 'Question 2',
            'question_type' => 'text',
            'is_required' => false,
            'id' => 'existing-id',
        ],
    ];

    $dtos = ScreeningQuestionDTO::collectionFromRepeater(
        repeaterData: $repeaterData,
        requisitionId: 'req-123',
        teamId: 'team-456',
    );

    expect($dtos)->toHaveCount(2)
        ->and($dtos[0]->displayOrder)->toBe(1)
        ->and($dtos[0]->isExisting())->toBeFalse()
        ->and($dtos[1]->displayOrder)->toBe(2)
        ->and($dtos[1]->isExisting())->toBeTrue();
});

it('can convert to array', function (): void {
    $dto = new ScreeningQuestionDTO(
        requisitionId: 'req-123',
        teamId: 'team-456',
        questionText: 'Test question',
        questionType: QuestionTypeEnum::Text,
        displayOrder: 1,
        isRequired: true,
        isKnockout: false,
    );

    $array = $dto->toArray();

    expect($array)->toHaveKeys([
        'requisition_id',
        'team_id',
        'question_text',
        'question_type',
        'display_order',
        'is_required',
        'is_knockout',
        'settings',
        'knockout_criteria',
    ])
        ->and($array['requisition_id'])->toBe('req-123')
        ->and($array['question_type'])->toBe(QuestionTypeEnum::Text);
});

it('correctly identifies existing vs new questions', function (): void {
    $newDto = new ScreeningQuestionDTO(
        requisitionId: 'req-123',
        teamId: 'team-456',
        questionText: 'New question',
        questionType: QuestionTypeEnum::Text,
        displayOrder: 1,
    );

    $existingDto = new ScreeningQuestionDTO(
        requisitionId: 'req-123',
        teamId: 'team-456',
        questionText: 'Existing question',
        questionType: QuestionTypeEnum::Text,
        displayOrder: 2,
        id: 'question-123',
    );

    expect($newDto->isExisting())->toBeFalse()
        ->and($existingDto->isExisting())->toBeTrue();
});
