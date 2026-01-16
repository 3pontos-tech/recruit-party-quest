<?php

declare(strict_types=1);

namespace He4rt\Admin\Tests\Feature\Filament;

use Filament\Facades\Filament;
use He4rt\Admin\Filament\Resources\JobRequisitions\JobRequisitionResource;
use He4rt\Admin\Filament\Resources\JobRequisitions\Pages\CreateJobRequisition;
use He4rt\Admin\Filament\Resources\JobRequisitions\Pages\EditJobRequisition;
use He4rt\Admin\Filament\Resources\JobRequisitions\Pages\ListJobRequisitions;
use He4rt\Permissions\Roles;
use He4rt\Recruitment\Requisitions\Enums\EmploymentTypeEnum;
use He4rt\Recruitment\Requisitions\Enums\ExperienceLevelEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionPriorityEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkArrangementEnum;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Teams\Department;
use He4rt\Teams\Team;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    actingAs(User::factory()->create());

    // Sync permissions so we have some to work with
    artisan('sync:permissions');

    // Give the user SuperAdmin role to bypass all policies
    auth()->user()->assignRole(Roles::SuperAdmin->value);
});

it('can list job requisitions', function (): void {
    $requisitions = JobRequisition::factory()->count(3)->create();

    livewire(ListJobRequisitions::class)
        ->assertCanSeeTableRecords($requisitions);
});

it('can render create job requisition page', function (): void {
    $this->get(JobRequisitionResource::getUrl('create'))
        ->assertSuccessful();
});

it('can render edit job requisition page', function (): void {
    $requisition = JobRequisition::factory()->create();

    $this->get(JobRequisitionResource::getUrl('edit', ['record' => $requisition]))
        ->assertSuccessful();
});

it('can create a job requisition', function (): void {
    $team = Team::factory()->create();
    $department = Department::factory()->create(['team_id' => $team->id]);
    $hiringManager = User::factory()->create();
    $team->members()->attach($hiringManager->id);

    livewire(CreateJobRequisition::class)
        ->fillForm([
            'team_id' => $team->id,
            'department_id' => $department->id,
            'hiring_manager_id' => $hiringManager->id,
            'status' => RequisitionStatusEnum::Draft,
            'priority' => RequisitionPriorityEnum::Medium,
            'work_arrangement' => WorkArrangementEnum::Remote,
            'employment_type' => EmploymentTypeEnum::FullTimeEmployee,
            'experience_level' => ExperienceLevelEnum::MidLevel,
            'positions_available' => 2,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('recruitment_job_requisitions', [
        'team_id' => $team->id,
        'department_id' => $department->id,
        'hiring_manager_id' => $hiringManager->id,
        'positions_available' => 2,
    ]);
});

it('can update a job requisition', function (): void {
    $department = Department::factory()->create();
    $requisition = JobRequisition::factory()
        ->recycle($department)
        ->recycle($department->team)
        ->create();

    livewire(EditJobRequisition::class, ['record' => $requisition->id])
        ->fillForm([
            'department_id' => $requisition->department_id,
            'positions_available' => 5,
            'priority' => RequisitionPriorityEnum::High,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('recruitment_job_requisitions', [
        'id' => $requisition->id,
        'positions_available' => 5,
        'priority' => RequisitionPriorityEnum::High->value,
    ]);
});

it('can filter job requisitions by status', function (): void {
    $draftRequisition = JobRequisition::factory()->create(['status' => RequisitionStatusEnum::Draft]);
    $publishedRequisition = JobRequisition::factory()->create(['status' => RequisitionStatusEnum::Published]);

    livewire(ListJobRequisitions::class)
        ->assertCanSeeTableRecords([$draftRequisition, $publishedRequisition])
        ->filterTable('status', RequisitionStatusEnum::Draft->value)
        ->assertCanSeeTableRecords([$draftRequisition])
        ->assertCanNotSeeTableRecords([$publishedRequisition]);
});
