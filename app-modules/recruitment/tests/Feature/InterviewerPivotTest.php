<?php

declare(strict_types=1);

use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Recruitment\Stages\Models\InterviewerPivot;
use He4rt\Recruitment\Stages\Models\Stage;

it('can create an interviewer pivot', function (): void {
    $pivot = InterviewerPivot::factory()->create();

    expect($pivot)->toBeInstanceOf(InterviewerPivot::class)
        ->and($pivot->id)->not->toBeNull()
        ->and($pivot->pipeline_stage_id)->not->toBeNull()
        ->and($pivot->recruiter_id)->not->toBeNull();
});

it('belongs to a stage', function (): void {
    $stage = Stage::factory()->create();
    $pivot = InterviewerPivot::factory()->create(['pipeline_stage_id' => $stage->id]);

    expect($pivot->stage)->toBeInstanceOf(Stage::class)
        ->and($pivot->stage->id)->toBe($stage->id);
});

it('belongs to an interviewer user', function (): void {
    $recruiter = Recruiter::factory()->create();
    $pivot = InterviewerPivot::factory()->create(['recruiter_id' => $recruiter->id]);

    expect($pivot->interviewer)->toBeInstanceOf(Recruiter::class)
        ->and($pivot->interviewer->id)->toBe($recruiter->id);
});

it('can assign multiple interviewers to a stage', function (): void {
    $stage = Stage::factory()->create();
    $recruiters = Recruiter::factory()->recycle($stage->team)->count(3)->create();

    foreach ($recruiters as $recruiter) {
        InterviewerPivot::factory()->create([
            'pipeline_stage_id' => $stage->getKey(),
            'recruiter_id' => $recruiter->getKey(),
        ]);
    }

    expect($stage->interviewers)->toHaveCount(3);
});
