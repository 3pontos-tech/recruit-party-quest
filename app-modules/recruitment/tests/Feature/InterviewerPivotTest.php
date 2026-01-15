<?php

declare(strict_types=1);

use He4rt\Recruitment\Stages\Models\InterviewerPivot;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Users\User;

it('can create an interviewer pivot', function (): void {
    $pivot = InterviewerPivot::factory()->create();

    expect($pivot)->toBeInstanceOf(InterviewerPivot::class)
        ->and($pivot->id)->not->toBeNull()
        ->and($pivot->pipeline_stage_id)->not->toBeNull()
        ->and($pivot->interviewer_user_id)->not->toBeNull();
});

it('belongs to a stage', function (): void {
    $stage = Stage::factory()->create();
    $pivot = InterviewerPivot::factory()->create(['pipeline_stage_id' => $stage->id]);

    expect($pivot->stage)->toBeInstanceOf(Stage::class)
        ->and($pivot->stage->id)->toBe($stage->id);
});

it('belongs to an interviewer user', function (): void {
    $user = User::factory()->create();
    $pivot = InterviewerPivot::factory()->create(['interviewer_user_id' => $user->id]);

    expect($pivot->interviewer)->toBeInstanceOf(User::class)
        ->and($pivot->interviewer->id)->toBe($user->id);
});

it('can assign multiple interviewers to a stage', function (): void {
    $stage = Stage::factory()->create();
    $users = User::factory()->count(3)->create();

    foreach ($users as $user) {
        InterviewerPivot::factory()->create([
            'pipeline_stage_id' => $stage->id,
            'interviewer_user_id' => $user->id,
        ]);
    }

    expect($stage->interviewers)->toHaveCount(3);
});
