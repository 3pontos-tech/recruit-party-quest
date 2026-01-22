<?php

declare(strict_types=1);

use He4rt\Recruitment\Requisitions\Enums\EmploymentTypeEnum;
use He4rt\Recruitment\Requisitions\Enums\ExperienceLevelEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionPriorityEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkArrangementEnum;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Screening\Models\ScreeningQuestion;
use He4rt\Teams\Department;
use He4rt\Teams\Team;
use He4rt\Users\User;

it('can create a job requisition', function (): void {
    $requisition = JobRequisition::factory()->create();

    expect($requisition)->toBeInstanceOf(JobRequisition::class)
        ->and($requisition->id)->not->toBeNull()
        ->and($requisition->slug)->not->toBeNull()
        ->and($requisition->status)->toBeInstanceOf(RequisitionStatusEnum::class)
        ->and($requisition->priority)->toBeInstanceOf(RequisitionPriorityEnum::class);
});

it('belongs to a team', function (): void {
    $team = Team::factory()->create();
    $requisition = JobRequisition::factory()->create(['team_id' => $team->id]);

    expect($requisition->team)->toBeInstanceOf(Team::class)
        ->and($requisition->team->id)->toBe($team->id);
});

it('belongs to a department', function (): void {
    $department = Department::factory()->create();
    $requisition = JobRequisition::factory()->create(['department_id' => $department->id]);

    expect($requisition->department)->toBeInstanceOf(Department::class)
        ->and($requisition->department->id)->toBe($department->id);
});

it('belongs to a hiring manager', function (): void {
    $user = User::factory()->create();
    $requisition = JobRequisition::factory()->create(['hiring_manager_id' => $user->id]);

    expect($requisition->hiringManager)->toBeInstanceOf(User::class)
        ->and($requisition->hiringManager->id)->toBe($user->id);
});

it('belongs to a created by user', function (): void {
    $user = User::factory()->create();
    $requisition = JobRequisition::factory()->create(['created_by_id' => $user->id]);

    expect($requisition->createdBy)->toBeInstanceOf(User::class)
        ->and($requisition->createdBy->id)->toBe($user->id);
});

it('has one job posting', function (): void {
    $requisition = JobRequisition::factory()->create();
    JobPosting::factory()->create(['job_requisition_id' => $requisition->id]);

    expect($requisition->post)->toBeInstanceOf(JobPosting::class);
});

it('has many screening questions', function (): void {
    $requisition = JobRequisition::factory()->create();
    ScreeningQuestion::factory()->count(3)->create([
        'screenable_id' => $requisition->id,
        'display_order' => fn () => fake()->unique()->numberBetween(1, 100),
    ]);

    expect($requisition->screeningQuestions)->toHaveCount(3);
});

it('casts work_arrangement to enum', function (): void {
    $requisition = JobRequisition::factory()->create();

    expect($requisition->work_arrangement)->toBeInstanceOf(WorkArrangementEnum::class);
});

it('casts employment_type to enum', function (): void {
    $requisition = JobRequisition::factory()->create();

    expect($requisition->employment_type)->toBeInstanceOf(EmploymentTypeEnum::class);
});

it('casts experience_level to enum', function (): void {
    $requisition = JobRequisition::factory()->create();

    expect($requisition->experience_level)->toBeInstanceOf(ExperienceLevelEnum::class);
});

it('casts is_internal_only to boolean', function (): void {
    $requisition = JobRequisition::factory()->create(['is_internal_only' => true]);

    expect($requisition->is_internal_only)->toBeBool();
});

it('casts is_confidential to boolean', function (): void {
    $requisition = JobRequisition::factory()->create(['is_confidential' => false]);

    expect($requisition->is_confidential)->toBeBool();
});

it('uses soft deletes', function (): void {
    $requisition = JobRequisition::factory()->create();
    $requisition->delete();

    expect($requisition->deleted_at)->not->toBeNull()
        ->and(JobRequisition::withTrashed()->find($requisition->id))->not->toBeNull();
});
