<?php

declare(strict_types=1);

use He4rt\Applications\Models\Application;
use He4rt\Feedback\Enums\EvaluationRatingEnum;
use He4rt\Feedback\Models\Evaluation;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Users\User;

it('can create an evaluation', function (): void {
    $evaluation = Evaluation::factory()->create();

    expect($evaluation)->toBeInstanceOf(Evaluation::class)
        ->and($evaluation->id)->not->toBeNull()
        ->and($evaluation->overall_rating)->toBeInstanceOf(EvaluationRatingEnum::class);
});

it('can create a submitted evaluation', function (): void {
    $evaluation = Evaluation::factory()->submitted()->create();

    expect($evaluation->submitted_at)->not->toBeNull();
});

it('can create a draft evaluation', function (): void {
    $evaluation = Evaluation::factory()->draft()->create();

    expect($evaluation->submitted_at)->toBeNull();
});

it('can create a positive evaluation', function (): void {
    $evaluation = Evaluation::factory()->positive()->create();

    expect($evaluation->overall_rating)->toBeIn([EvaluationRatingEnum::Yes, EvaluationRatingEnum::StrongYes]);
});

it('can create a negative evaluation', function (): void {
    $evaluation = Evaluation::factory()->negative()->create();

    expect($evaluation->overall_rating)->toBeIn([EvaluationRatingEnum::No, EvaluationRatingEnum::StrongNo]);
});

it('belongs to an application', function (): void {
    $application = Application::factory()->create();
    $evaluation = Evaluation::factory()->create(['application_id' => $application->id]);

    expect($evaluation->application)->toBeInstanceOf(Application::class)
        ->and($evaluation->application->id)->toBe($application->id);
});

it('belongs to a stage', function (): void {
    $stage = Stage::factory()->create();
    $evaluation = Evaluation::factory()->create(['stage_id' => $stage->id]);

    expect($evaluation->stage)->toBeInstanceOf(Stage::class)
        ->and($evaluation->stage->id)->toBe($stage->id);
});

it('belongs to an evaluator', function (): void {
    $user = User::factory()->create();
    $evaluation = Evaluation::factory()->create(['evaluator_id' => $user->id]);

    expect($evaluation->evaluator)->toBeInstanceOf(User::class)
        ->and($evaluation->evaluator->id)->toBe($user->id);
});

it('can be accessed from application', function (): void {
    $application = Application::factory()->create();
    Evaluation::factory()->count(3)->create(['application_id' => $application->id]);

    expect($application->evaluations)->toHaveCount(3);
});

it('stores criteria scores as array', function (): void {
    $criteriaScores = [
        'communication' => 4,
        'technical_skills' => 5,
        'problem_solving' => 3,
    ];

    $evaluation = Evaluation::factory()->create(['criteria_scores' => $criteriaScores]);

    expect($evaluation->criteria_scores)->toBeArray()
        ->and($evaluation->criteria_scores)->toBe($criteriaScores);
});

it('has correct color for each rating', function (): void {
    foreach (EvaluationRatingEnum::cases() as $rating) {
        expect($rating->getColor())->toBeArray();
    }
});

it('has correct label for each rating', function (): void {
    expect(EvaluationRatingEnum::StrongNo->getLabel())->toBe('Strong No')
        ->and(EvaluationRatingEnum::No->getLabel())->toBe('No')
        ->and(EvaluationRatingEnum::Maybe->getLabel())->toBe('Maybe')
        ->and(EvaluationRatingEnum::Yes->getLabel())->toBe('Yes')
        ->and(EvaluationRatingEnum::StrongYes->getLabel())->toBe('Strong Yes');
});
