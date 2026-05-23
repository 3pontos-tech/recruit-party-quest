<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Admin\Filament\Resources\Recruitment\JobRequisitions\Pages\EditJobRequisition;
use He4rt\Admin\Filament\Resources\Recruitment\JobRequisitions\Pages\ListJobRequisitions;
use He4rt\Recruitment\Requisitions\Enums\EmploymentTypeEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkArrangementEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkScheduleEnum;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Teams\Department;
use He4rt\Teams\Team;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    filament()->setCurrentPanel(FilamentPanel::Admin->value);

    actingAs(User::factory()->admin()->create());
});

it('can render edit job requisition page', function (): void {
    $requisition = JobRequisition::factory()->create();

    livewire(EditJobRequisition::class, ['record' => $requisition->getRouteKey()])
        ->assertOk();
});

it('edit form is pre-populated with correct data', function (): void {
    $requisition = JobRequisition::factory()->create([
        'status' => RequisitionStatusEnum::Draft,
        'work_arrangement' => WorkArrangementEnum::Remote,
        'employment_type' => EmploymentTypeEnum::Clt,
    ]);

    livewire(EditJobRequisition::class, ['record' => $requisition->getRouteKey()])
        ->assertFormSet([
            'status' => RequisitionStatusEnum::Draft,
            'work_arrangement' => WorkArrangementEnum::Remote,
            'employment_type' => EmploymentTypeEnum::Clt,
        ]);
});

it('allows saving a legacy requisition with null employment_type and work_schedule on edit', function (): void {
    $team = Team::factory()->create();
    $recruiter = Recruiter::factory()->for($team, 'team')->create();
    $department = Department::factory()
        ->for($team, 'team')
        ->state(['head_user_id' => $recruiter->user_id])
        ->create();

    $requisition = JobRequisition::factory()
        ->for($team)
        ->for($department)
        ->for($recruiter, 'recruiter')
        ->for($recruiter->user, 'createdBy')
        ->create([
            'employment_type' => null,
            'work_schedule' => null,
        ]);

    livewire(EditJobRequisition::class, ['record' => $requisition->getRouteKey()])
        ->call('save')
        ->assertHasNoFormErrors();
});

it('lists requisitions with null employment_type without breaking', function (): void {
    JobRequisition::factory()->create([
        'employment_type' => null,
        'work_schedule' => WorkScheduleEnum::FullTime,
    ]);

    livewire(ListJobRequisitions::class)
        ->assertOk()
        ->assertTableColumnExists('work_schedule');
});
