<?php

declare(strict_types=1);

use He4rt\Recruitment\Requisitions\Enums\EmploymentTypeEnum;
use He4rt\Recruitment\Requisitions\Enums\ExperienceLevelEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionPriorityEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkArrangementEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkScheduleEnum;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Recruitment\Stages\Enums\StageTypeEnum;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Screening\Models\ScreeningQuestion;
use He4rt\Teams\Department;
use He4rt\Teams\Team;
use He4rt\Users\User;

it('can create a job requisition', function (): void {
    $requisition = JobRequisition::factory()->create();

    expect($requisition)->toBeInstanceOf(JobRequisition::class)
        ->and($requisition->getKey())->not->toBeNull()
        ->and($requisition->slug)->not->toBeNull()
        ->and($requisition->status)->toBeInstanceOf(RequisitionStatusEnum::class)
        ->and($requisition->priority)->toBeInstanceOf(RequisitionPriorityEnum::class);
});

it('belongs to a team', function (): void {
    $team = Team::factory()->create();
    $requisition = JobRequisition::factory()->create(['team_id' => $team->getKey()]);

    expect($requisition->team)->toBeInstanceOf(Team::class)
        ->and($requisition->team->getKey())->toBe($team->getKey());
});

it('belongs to a department', function (): void {
    $department = Department::factory()->create();
    $requisition = JobRequisition::factory()->create(['department_id' => $department->getKey()]);

    expect($requisition->department)->toBeInstanceOf(Department::class)
        ->and($requisition->department->getKey())->toBe($department->getKey());
});

it('belongs to a recruiter', function (): void {
    $team = Team::factory()->create();
    $recruiter = Recruiter::factory()->create(['team_id' => $team->getKey()]);
    $requisition = JobRequisition::factory()->create(['team_id' => $team->getKey(), 'recruiter_id' => $recruiter->getKey()]);

    expect($requisition->recruiter)->toBeInstanceOf(Recruiter::class)
        ->and($requisition->recruiter->getKey())->toBe($recruiter->getKey());
});

it('belongs to a created by user', function (): void {
    $user = User::factory()->create();
    $requisition = JobRequisition::factory()->create(['created_by_id' => $user->getKey()]);

    expect($requisition->createdBy)->toBeInstanceOf(User::class)
        ->and($requisition->createdBy->getKey())->toBe($user->getKey());
});

it('has one job posting', function (): void {
    $requisition = JobRequisition::factory()->create();
    JobPosting::factory()->create(['job_requisition_id' => $requisition->getKey()]);

    expect($requisition->post)->toBeInstanceOf(JobPosting::class);
});

it('has many screening questions', function (): void {
    $requisition = JobRequisition::factory()->create();
    ScreeningQuestion::factory()->count(3)->create([
        'screenable_id' => $requisition->getKey(),
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
        ->and(JobRequisition::withTrashed()->find($requisition->getKey()))->not->toBeNull();
});

it('automatically creates 8 default stages when created', function (): void {
    $requisition = JobRequisition::factory()->create();

    expect($requisition->stages)->toHaveCount(8);
});

it('creates stages with correct stage types', function (): void {
    $requisition = JobRequisition::factory()->create();

    $stageTypes = $requisition->stages->pluck('stage_type')->toArray();

    expect($stageTypes)->toContain(StageTypeEnum::New)
        ->and($stageTypes)->toContain(StageTypeEnum::Screening)
        ->and($stageTypes)->toContain(StageTypeEnum::Assessment)
        ->and($stageTypes)->toContain(StageTypeEnum::Interview)
        ->and($stageTypes)->toContain(StageTypeEnum::Offer)
        ->and($stageTypes)->toContain(StageTypeEnum::Hired)
        ->and($stageTypes)->toContain(StageTypeEnum::Declined)
        ->and($stageTypes)->toContain(StageTypeEnum::Rejected);
});

it('creates stages with correct display order', function (): void {
    $requisition = JobRequisition::factory()->create();

    $displayOrders = $requisition->stages->pluck('display_order')->sort()->values()->toArray();

    expect($displayOrders)->toBe([1, 2, 3, 4, 5, 6, 7, 8]);
});

it('creates stages with correct team_id', function (): void {
    $requisition = JobRequisition::factory()->create();

    $requisition->stages->each(function (Stage $stage) use ($requisition): void {
        expect($stage->team_id)->toBe($requisition->team_id);
    });
});

it('creates stages that are active and visible by default', function (): void {
    $requisition = JobRequisition::factory()->create();

    $requisition->stages->each(function (Stage $stage): void {
        expect($stage->active)->toBeTrue()
            ->and($stage->hidden)->toBeFalse();
    });
});

it('enforces slug pattern with team slug on creation', function (): void {
    $team = Team::factory()->create(['slug' => 'acme-corp']);
    $requisition = JobRequisition::factory()->create([
        'team_id' => $team->getKey(),
        'slug' => 'senior-developer-ab1x',
    ]);

    expect($requisition->slug)->toMatch('/^senior-developer-acme-corp-[a-z0-9]{5}$/');
});

it('preserves slug when it already follows the expected pattern', function (): void {
    $team = Team::factory()->create(['slug' => 'acme-corp']);
    $validSlug = 'senior-developer-acme-corp-ab1x9';
    $requisition = JobRequisition::factory()->create([
        'team_id' => $team->getKey(),
        'slug' => $validSlug,
    ]);

    expect($requisition->slug)->toBe($validSlug);
});

it('enforces slug pattern even with uuid-style slugs from factory', function (): void {
    $team = Team::factory()->create(['slug' => 'my-team']);
    $requisition = JobRequisition::factory()->create([
        'team_id' => $team->getKey(),
    ]);

    expect($requisition->slug)->toEndWith('-my-team-'.mb_substr($requisition->slug, -5))
        ->and($requisition->slug)->toMatch('/-my-team-[a-z0-9]{5}$/');
});

it('creates stages with expected names and descriptions', function (): void {
    $requisition = JobRequisition::factory()->create();

    $newStage = $requisition->stages->firstWhere('stage_type', StageTypeEnum::New);
    $screeningStage = $requisition->stages->firstWhere('stage_type', StageTypeEnum::Screening);
    $offerStage = $requisition->stages->firstWhere('stage_type', StageTypeEnum::Offer);

    expect($newStage->name)->toBe('New Applications')
        ->and($newStage->description)->toBe('Initial application submission and registration')
        ->and($newStage->expected_duration_days)->toBe(1)
        ->and($screeningStage->name)->toBe('Resume Screening')
        ->and($screeningStage->description)->toBe('Review of resume and basic qualifications')
        ->and($screeningStage->expected_duration_days)->toBe(3)
        ->and($offerStage->name)->toBe('Offer')
        ->and($offerStage->description)->toBe('Job offer preparation and negotiation')
        ->and($offerStage->expected_duration_days)->toBe(5);
});

it('creates default stages translated when the locale is pt_BR', function (): void {
    app()->setLocale('pt_BR');

    $requisition = JobRequisition::factory()->create();

    $newStage = $requisition->stages->firstWhere('stage_type', StageTypeEnum::New);
    $screeningStage = $requisition->stages->firstWhere('stage_type', StageTypeEnum::Screening);
    $offerStage = $requisition->stages->firstWhere('stage_type', StageTypeEnum::Offer);

    expect($newStage->name)->toBe('Novas Candidaturas')
        ->and($newStage->description)->toBe('Recebimento e registro inicial das candidaturas')
        ->and($screeningStage->name)->toBe('Triagem de Currículos')
        ->and($offerStage->name)->toBe('Proposta');
});

it('casts work_schedule and allows null employment_type', function (): void {
    $model = JobRequisition::factory()->create([
        'employment_type' => null,
        'work_schedule' => WorkScheduleEnum::FullTime,
    ]);

    $model->refresh();

    expect($model->employment_type)->toBeNull()
        ->and($model->work_schedule)->toBe(WorkScheduleEnum::FullTime);
});

it('persists work_schedule and null employment_type via StoreJobRequisitionAction', function (): void {
    $team = He4rt\Teams\Team::factory()->create();
    $dept = He4rt\Teams\Department::factory()->create(['team_id' => $team->id]);
    $recruiter = He4rt\Recruitment\Staff\Recruiter\Recruiter::factory()->create();
    $user = He4rt\Users\User::factory()->create();

    $dto = He4rt\Recruitment\Requisitions\DTOs\JobRequisitionDTO::make([
        'title' => 'Dev Backend', 'department_id' => $dept->id, 'team_id' => $team->id,
        'recruiter_id' => $recruiter->id, 'description' => 'desc',
        'experience_level' => He4rt\Recruitment\Requisitions\Enums\ExperienceLevelEnum::Senior,
        'employment_type' => null,
        'work_schedule' => He4rt\Recruitment\Requisitions\Enums\WorkScheduleEnum::PartTime,
        'work_arrangement' => He4rt\Recruitment\Requisitions\Enums\WorkArrangementEnum::Remote,
        'priority' => He4rt\Recruitment\Requisitions\Enums\RequisitionPriorityEnum::Medium,
        'status' => He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum::Draft,
        'summary' => 'sum', 'created_by' => $user->id, 'items' => [],
    ]);

    $req = resolve(He4rt\Recruitment\Requisitions\Actions\StoreJobRequisitionAction::class)->execute($dto);

    expect($req->fresh()->work_schedule)->toBe(He4rt\Recruitment\Requisitions\Enums\WorkScheduleEnum::PartTime)
        ->and($req->fresh()->employment_type)->toBeNull();
});

it('factory produces valid employment_type and work_schedule', function (): void {
    He4rt\Recruitment\Requisitions\Models\JobRequisition::factory()->count(20)->create()->each(function ($r): void {
        expect($r->employment_type === null || $r->employment_type instanceof He4rt\Recruitment\Requisitions\Enums\EmploymentTypeEnum)->toBeTrue()
            ->and($r->work_schedule === null || $r->work_schedule instanceof He4rt\Recruitment\Requisitions\Enums\WorkScheduleEnum)->toBeTrue();
    });
});
