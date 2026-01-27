<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use He4rt\Admin\Filament\Resources\Recruitment\JobRequisitions\Pages\CreateJobRequisition;
use He4rt\Permissions\Roles;
use He4rt\Recruitment\Requisitions\Enums\EmploymentTypeEnum;
use He4rt\Recruitment\Requisitions\Enums\ExperienceLevelEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkArrangementEnum;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Teams\Department;
use He4rt\Teams\Team;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    actingAs(User::factory()->create());

    // Sync permissions so we have some to work with
    artisan('sync:permissions');

    // Give the user SuperAdmin role to bypass all policies
    auth()->user()->assignRole(Roles::SuperAdmin->value);

    $this->team = Team::factory()->create();
    $this->recruiter = Recruiter::factory()
        ->for($this->team, 'team')
        ->create();
    $this->department = Department::factory()
        ->for($this->team, 'team')
        ->state(['head_user_id' => $this->recruiter->user_id])
        ->create();
});

it('should render ', function (): void {
    livewire(CreateJobRequisition::class)
        ->assertOk();
});

it('should be able to publish a job requisition', function (): void {
    $workArrangement = WorkArrangementEnum::Hybrid;
    $employmentType = EmploymentTypeEnum::Contractor;
    $experienceLevel = ExperienceLevelEnum::Lead;

    livewire(CreateJobRequisition::class)
        ->assertOk()
        ->set('data.team_id', $this->team->getKey())
        ->assertSet('data.team_id', $this->team->getKey())
        ->set('data.recruiter_id', $this->recruiter->getKey())
        ->assertSet('data.recruiter_id', $this->recruiter->getKey())
        ->set('data.department_id', $this->department->getKey())
        ->assertSet('data.department_id', $this->department->getKey())
        ->set('data.work_arrangement', $workArrangement)
        ->assertSet('data.work_arrangement', $workArrangement->value)
        ->set('data.employment_type', $employmentType)
        ->assertSet('data.employment_type', $employmentType->value)
        ->set('data.experience_level', $experienceLevel)
        ->assertSet('data.experience_level', $experienceLevel->value)
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseCount(JobRequisition::class, 1);
    assertDatabaseHas(JobRequisition::class, [
        'team_id' => $this->team->getKey(),
        'recruiter_id' => $this->recruiter->getKey(),
        'department_id' => $this->department->getKey(),
        'work_arrangement' => $workArrangement->value,
        'employment_type' => $employmentType->value,
        'experience_level' => $experienceLevel->value,
    ]);

});
