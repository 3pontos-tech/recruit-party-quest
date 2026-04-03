<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages\CreateJobRequisition;
use He4rt\Recruitment\Requisitions\Enums\EmploymentTypeEnum;
use He4rt\Recruitment\Requisitions\Enums\ExperienceLevelEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionPriorityEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkArrangementEnum;
use He4rt\Recruitment\Requisitions\Jobs\GeneratePostJob;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Teams\Department;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    filament()->setCurrentPanel(FilamentPanel::Organization->value);
    $this->recruiter = Recruiter::factory()->createOne();
    actingAs($this->recruiter->user);
    $this->team = $this->recruiter->team;
    $this->department = Department::factory()->forRecruiter($this->recruiter)->createOne();
    filament()->setTenant($this->team);
    $this->recruiter->user->givePermissionTo('create_job_requisitions');

    $this->actionData = [
        'title' => 'Dev PHP Sênior',
        'description' => '<p>Desenvolvedor experiente com Laravel.</p>',
        'department_id' => $this->department->getKey(),
        'recruiter_id' => $this->recruiter->getKey(),
        'work_arrangement' => WorkArrangementEnum::Remote,
        'employment_type' => EmploymentTypeEnum::FullTimeEmployee,
        'experience_level' => ExperienceLevelEnum::Senior,
        'priority' => RequisitionPriorityEnum::Medium,
    ];
});

it('dispatches GeneratePostJob to the queue when the queue connection is healthy', function (): void {
    Queue::fake();

    Livewire::test(CreateJobRequisition::class)
        ->callAction('generate-job-requisition-action', data: $this->actionData)
        ->assertHasNoActionErrors();

    Queue::assertPushed(GeneratePostJob::class);
});

it('dispatches GeneratePostJob with the correct DTO data', function (): void {
    Queue::fake();

    Livewire::test(CreateJobRequisition::class)
        ->callAction('generate-job-requisition-action', data: $this->actionData)
        ->assertHasNoActionErrors();

    Queue::assertPushed(GeneratePostJob::class, fn (GeneratePostJob $job): bool => $job->dto->teamId === $this->team->getKey()
        && $job->dto->departmentId === $this->department->getKey()
        && $job->dto->recruiterId === $this->recruiter->getKey());
});

it('shows an error notification and does not dispatch the job when the queue connection fails', function (): void {
    config(['queue.default' => 'nonexistent_queue_connection_xyz']);

    Livewire::test(CreateJobRequisition::class)
        ->callAction('generate-job-requisition-action', data: $this->actionData)
        ->assertNotified();
});
