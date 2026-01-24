<?php

declare(strict_types=1);

use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Recruitment\Stages\Enums\StageTypeEnum;
use He4rt\Recruitment\Stages\Models\InterviewerPivot;
use He4rt\Recruitment\Stages\Models\Stage;

it('can create a stage', function (): void {
    $stage = Stage::factory()->create();

    expect($stage)->toBeInstanceOf(Stage::class)
        ->and($stage->id)->not->toBeNull()
        ->and($stage->name)->not->toBeNull()
        ->and($stage->stage_type)->toBeInstanceOf(StageTypeEnum::class);
});

it('belongs to a job requisition', function (): void {
    $requisition = JobRequisition::factory()->create();
    $stage = Stage::factory()->create(['job_requisition_id' => $requisition->id]);

    expect($stage->requisition)->toBeInstanceOf(JobRequisition::class)
        ->and($stage->requisition->id)->toBe($requisition->id);
});

it('has many interviewers', function (): void {
    $stage = Stage::factory()->create();
    $recruiters = Recruiter::factory()->recycle($stage->team)->count(2)->create();

    foreach ($recruiters as $recruiter) {
        InterviewerPivot::factory()->create([
            'pipeline_stage_id' => $stage->getKey(),
            'recruiter_id' => $recruiter->getKey(),
        ]);
    }

    expect($stage->interviewers)->toHaveCount(2);
});

it('casts stage_type to enum', function (): void {
    $stage = Stage::factory()->create();

    expect($stage->stage_type)->toBeInstanceOf(StageTypeEnum::class);
});

it('casts active to boolean', function (): void {
    $stage = Stage::factory()->create(['active' => true]);

    expect($stage->active)->toBeBool();
});

it('has display order', function (): void {
    $stage = Stage::factory()->create(['display_order' => 5]);

    expect($stage->display_order)->toBe(5);
});

it('has expected duration days', function (): void {
    $stage = Stage::factory()->create(['expected_duration_days' => 7]);

    expect($stage->expected_duration_days)->toBe(7);
});

it('uses soft deletes', function (): void {
    $stage = Stage::factory()->create();
    $stage->delete();

    expect($stage->deleted_at)->not->toBeNull()
        ->and(Stage::withTrashed()->find($stage->id))->not->toBeNull();
});
