<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use Filament\Actions\Testing\TestAction;
use He4rt\Applications\Models\Application;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Actions\DuplicateJobRequisitionAction;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\JobRequisitionResource;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages\EditJobRequisition;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages\ListJobRequisitions;
use He4rt\Permissions\Roles;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Teams\Department;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    filament()->setCurrentPanel(FilamentPanel::Organization->value);
    $this->recruiter = Recruiter::factory()->createOne();
    actingAs($this->recruiter->user);
    $this->team = $this->recruiter->team;
    $this->department = Department::factory()->forRecruiter($this->recruiter)->createOne();
    filament()->setTenant($this->team);

    $this->jobRequisition = JobRequisition::factory()
        ->for($this->team)
        ->for($this->department)
        ->for($this->recruiter, 'recruiter')
        ->for($this->recruiter->user, 'createdBy')
        ->create(['status' => RequisitionStatusEnum::Published]);

    $this->jobPosting = JobPosting::factory()
        ->for($this->jobRequisition, 'jobRequisition')
        ->create();
});

it('duplicates a job requisition from the table and redirects to the new draft', function (): void {
    $this->recruiter->user->givePermissionTo('create_job_requisitions');

    $component = Livewire::test(ListJobRequisitions::class)
        ->callAction(TestAction::make('duplicate')->table($this->jobRequisition))
        ->assertHasNoActionErrors();

    $duplicate = JobRequisition::query()->whereKeyNot($this->jobRequisition->getKey())->sole();

    expect($duplicate->status)->toBe(RequisitionStatusEnum::Draft)
        ->and($duplicate->created_by_id)->toBe($this->recruiter->user->getKey())
        ->and($duplicate->team_id)->toBe($this->team->getKey());

    $component->assertRedirect(JobRequisitionResource::getUrl('edit', ['record' => $duplicate]));
});

it('duplicates a job requisition from the edit page header action', function (): void {
    $this->recruiter->user->givePermissionTo('update_job_requisitions');
    $this->recruiter->user->givePermissionTo('create_job_requisitions');

    Livewire::test(EditJobRequisition::class, ['record' => $this->jobRequisition->getKey()])
        ->callAction(DuplicateJobRequisitionAction::class)
        ->assertHasNoActionErrors();

    expect(JobRequisition::query()->whereKeyNot($this->jobRequisition->getKey())->sole()->status)
        ->toBe(RequisitionStatusEnum::Draft);
});

it('hides the duplicate action when the user lacks the create permission', function (): void {
    Livewire::test(ListJobRequisitions::class)
        ->assertActionHidden(TestAction::make('duplicate')->table($this->jobRequisition));
});

it('hides the duplicate action for trashed requisitions', function (): void {
    $this->recruiter->user->givePermissionTo('create_job_requisitions');
    $this->jobRequisition->delete();

    Livewire::test(ListJobRequisitions::class)
        ->filterTable('trashed')
        ->assertActionHidden(TestAction::make('duplicate')->table($this->jobRequisition));
});

it('does not copy applications when duplicating', function (): void {
    $this->recruiter->user->givePermissionTo('create_job_requisitions');
    Application::factory()->for($this->jobRequisition, 'requisition')->create();

    Livewire::test(ListJobRequisitions::class)
        ->callAction(TestAction::make('duplicate')->table($this->jobRequisition))
        ->assertHasNoActionErrors();

    $duplicate = JobRequisition::query()->whereKeyNot($this->jobRequisition->getKey())->sole();

    expect($duplicate->applications()->count())->toBe(0);
});

it('bulk-duplicates the selected job requisitions as drafts', function (): void {
    $this->recruiter->user->assignRole(Roles::SuperAdmin->value);
    $this->recruiter->user->givePermissionTo('create_job_requisitions');

    $other = JobRequisition::factory()
        ->for($this->team)
        ->for($this->department)
        ->for($this->recruiter, 'recruiter')
        ->for($this->recruiter->user, 'createdBy')
        ->create(['status' => RequisitionStatusEnum::Published]);

    JobPosting::factory()->for($other, 'jobRequisition')->create();

    Livewire::test(ListJobRequisitions::class)
        ->selectTableRecords([$this->jobRequisition->getKey(), $other->getKey()])
        ->callAction(TestAction::make('duplicate')->table()->bulk())
        ->assertHasNoActionErrors();

    $duplicates = JobRequisition::query()
        ->whereNotIn('id', [$this->jobRequisition->getKey(), $other->getKey()])
        ->get();

    expect($duplicates)->toHaveCount(2)
        ->and($duplicates->pluck('status')->unique()->all())->toBe([RequisitionStatusEnum::Draft])
        ->and($duplicates->pluck('created_by_id')->unique()->all())->toBe([$this->recruiter->user->getKey()]);
});

it('hides the bulk duplicate action for users that are not admins', function (): void {
    $this->recruiter->user->givePermissionTo('create_job_requisitions');

    Livewire::test(ListJobRequisitions::class)
        ->assertActionHidden(TestAction::make('duplicate')->table()->bulk());
});

it('skips trashed requisitions when bulk-duplicating', function (): void {
    $this->recruiter->user->assignRole(Roles::SuperAdmin->value);
    $this->recruiter->user->givePermissionTo('create_job_requisitions');

    $trashed = JobRequisition::factory()
        ->for($this->team)
        ->for($this->department)
        ->for($this->recruiter, 'recruiter')
        ->for($this->recruiter->user, 'createdBy')
        ->create(['status' => RequisitionStatusEnum::Published]);

    JobPosting::factory()->for($trashed, 'jobRequisition')->create();
    $trashed->delete();

    Livewire::test(ListJobRequisitions::class)
        ->filterTable('trashed', true)
        ->selectTableRecords([$this->jobRequisition->getKey(), $trashed->getKey()])
        ->callAction(TestAction::make('duplicate')->table()->bulk())
        ->assertHasNoActionErrors();

    expect(JobRequisition::query()->whereNotIn('id', [$this->jobRequisition->getKey(), $trashed->getKey()])->count())
        ->toBe(1);
});
