<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages\CreateJobRequisition;
use He4rt\Recruitment\Requisitions\Enums\EmploymentTypeEnum;
use He4rt\Recruitment\Requisitions\Enums\ExperienceLevelEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionPriorityEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkArrangementEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkScheduleEnum;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Teams\Department;
use He4rt\Users\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function (): void {
    filament()->setCurrentPanel(FilamentPanel::Organization->value);
    $this->recruiter = Recruiter::factory()->createOne();
    actingAs($this->recruiter->user);
    $this->team = $this->recruiter->team;
    $this->department = Department::factory()->forRecruiter($this->recruiter)->createOne();
    filament()->setTenant($this->team);
    $this->recruiter->user->givePermissionTo('create_job_requisitions');
});

it('renders the create page successfully', function (): void {
    Livewire::test(CreateJobRequisition::class)
        ->assertOk();
});

it('denies access to users without create permission', function (): void {
    actingAs(User::factory()->createQuietly());

    Livewire::test(CreateJobRequisition::class)
        ->assertForbidden();
});

it('creates a job requisition and assigns team and creator automatically', function (): void {
    Livewire::test(CreateJobRequisition::class)
        ->fillForm([
            'post' => [
                'title' => 'Dev PHP Sênior',
                'summary' => 'Desenvolvedor PHP experiente para nosso time.',
                'description' => 'Responsável por manter e evoluir nossa stack.',
            ],
            'department_id' => $this->department->getKey(),
            'recruiter_id' => $this->recruiter->getKey(),
            'work_arrangement' => WorkArrangementEnum::Remote,
            'employment_type' => EmploymentTypeEnum::Clt,
            'work_schedule' => WorkScheduleEnum::FullTime,
            'experience_level' => ExperienceLevelEnum::Senior,
            'status' => RequisitionStatusEnum::Draft,
            'priority' => RequisitionPriorityEnum::Medium,
            'positions_available' => 1,
            'responsibilities' => [['content' => 'Desenvolver e manter aplicações Laravel', 'tags' => []]],
            'requiredQualifications' => [['content' => 'Experiência com PHP 8+', 'tags' => []]],
            'preferredQualifications' => [['content' => 'Conhecimento em Docker', 'tags' => []]],
            'benefits' => [['content' => 'Trabalho 100% remoto', 'tags' => []]],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(JobRequisition::class, [
        'team_id' => $this->team->getKey(),
        'created_by_id' => $this->recruiter->user->getKey(),
        'department_id' => $this->department->getKey(),
        'work_arrangement' => WorkArrangementEnum::Remote,
        'employment_type' => EmploymentTypeEnum::Clt,
        'experience_level' => ExperienceLevelEnum::Senior,
    ]);
});

it('generates a slug with the team initial and random suffix on creation', function (): void {
    Livewire::test(CreateJobRequisition::class)
        ->fillForm([
            'post' => [
                'title' => 'Dev PHP Sênior',
                'summary' => 'Sumário do cargo.',
                'description' => 'Descrição completa do cargo.',
            ],
            'department_id' => $this->department->getKey(),
            'recruiter_id' => $this->recruiter->getKey(),
            'work_arrangement' => WorkArrangementEnum::Remote,
            'employment_type' => EmploymentTypeEnum::Clt,
            'work_schedule' => WorkScheduleEnum::FullTime,
            'experience_level' => ExperienceLevelEnum::Senior,
            'status' => RequisitionStatusEnum::Draft,
            'priority' => RequisitionPriorityEnum::Medium,
            'positions_available' => 1,
            'responsibilities' => [['content' => 'Desenvolver aplicações', 'tags' => []]],
            'requiredQualifications' => [['content' => 'PHP avançado', 'tags' => []]],
            'preferredQualifications' => [['content' => 'Docker', 'tags' => []]],
            'benefits' => [['content' => 'Remoto', 'tags' => []]],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    // Observer normalizes slug to: {first-letter}-{team-slug}-{5-char-random}.
    // The mutateFormDataBeforeCreate sets "{letter}-{random5}", then the observer
    // extracts the part before the last dash and appends "{teamSlug}-{random5}".
    $jobRequisition = JobRequisition::query()->latest()->first();
    $teamSlug = $this->team->slug;
    expect($jobRequisition->slug)
        ->toContain($teamSlug)
        ->toMatch('/-[a-z0-9]{5}$/');
});

it('shows validation errors for required fields when submitting without data', function (): void {
    // The form closures (e.g. afterStateUpdated on title) have strict type hints that
    // prevent setting fields to null in fillForm. Calling create directly with no
    // data filled triggers the required validation for all mandatory fields.
    Livewire::test(CreateJobRequisition::class)
        ->call('create')
        ->assertHasFormErrors(['post.title', 'post.summary', 'department_id', 'recruiter_id']);
});

it('requires employment_type and work_schedule when creating', function (): void {
    Livewire::test(CreateJobRequisition::class)
        ->fillForm([
            'post' => [
                'title' => 'Dev PHP Sênior',
                'summary' => 'Sumário do cargo.',
                'description' => 'Descrição completa do cargo.',
            ],
            'department_id' => $this->department->getKey(),
            'recruiter_id' => $this->recruiter->getKey(),
            'work_arrangement' => WorkArrangementEnum::Remote,
            'experience_level' => ExperienceLevelEnum::Senior,
            'status' => RequisitionStatusEnum::Draft,
            'priority' => RequisitionPriorityEnum::Medium,
            'positions_available' => 1,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'employment_type' => 'required',
            'work_schedule' => 'required',
        ]);
});
