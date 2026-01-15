<?php

declare(strict_types=1);

use He4rt\Applications\Models\Application;
use He4rt\Applications\Models\ApplicationStageHistory;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Users\User;

it('can create an application stage history', function (): void {
    $history = ApplicationStageHistory::factory()->create();

    expect($history)->toBeInstanceOf(ApplicationStageHistory::class)
        ->and($history->id)->not->toBeNull()
        ->and($history->notes)->not->toBeNull();
});

it('belongs to an application', function (): void {
    $application = Application::factory()->create();
    $history = ApplicationStageHistory::factory()->create(['application_id' => $application->id]);

    expect($history->application)->toBeInstanceOf(Application::class)
        ->and($history->application->id)->toBe($application->id);
});

it('belongs to a from stage', function (): void {
    $stage = Stage::factory()->create();
    $history = ApplicationStageHistory::factory()->create(['from_stage_id' => $stage->id]);

    expect($history->fromStage)->toBeInstanceOf(Stage::class)
        ->and($history->fromStage->id)->toBe($stage->id);
});

it('belongs to a to stage', function (): void {
    $stage = Stage::factory()->create();
    $history = ApplicationStageHistory::factory()->create(['to_stage_id' => $stage->id]);

    expect($history->toStage)->toBeInstanceOf(Stage::class)
        ->and($history->toStage->id)->toBe($stage->id);
});

it('belongs to a moved by user', function (): void {
    $user = User::factory()->create();
    $history = ApplicationStageHistory::factory()->create(['moved_by' => $user->id]);

    expect($history->movedBy)->toBeInstanceOf(User::class)
        ->and($history->movedBy->id)->toBe($user->id);
});

it('can be accessed from application', function (): void {
    $application = Application::factory()->create();
    ApplicationStageHistory::factory()->count(3)->create(['application_id' => $application->id]);

    expect($application->stageHistory)->toHaveCount(3);
});

it('can have null from stage for initial entry', function (): void {
    $history = ApplicationStageHistory::factory()->create(['from_stage_id' => null]);

    expect($history->from_stage_id)->toBeNull()
        ->and($history->fromStage)->toBeNull();
});
