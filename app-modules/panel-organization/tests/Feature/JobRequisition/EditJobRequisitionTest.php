<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages\EditJobRequisition;
use He4rt\Recruitment\Requisitions\Enums\RequisitionPriorityEnum;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Teams\Department;
use He4rt\Users\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertSoftDeleted;

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
        ->create();

    $this->jobPosting = JobPosting::factory()
        ->for($this->jobRequisition, 'jobRequisition')
        ->create();
});

it('renders the edit page successfully', function (): void {
    $this->recruiter->user->givePermissionTo('update_job_requisitions');

    Livewire::test(EditJobRequisition::class, ['record' => $this->jobRequisition->getKey()])
        ->assertOk();
});

it('displays the job posting title as the page heading', function (): void {
    $this->recruiter->user->givePermissionTo('update_job_requisitions');

    Livewire::test(EditJobRequisition::class, ['record' => $this->jobRequisition->getKey()])
        ->assertOk()
        ->assertSee($this->jobPosting->title);
});

it('denies access to users without update permission', function (): void {
    actingAs(User::factory()->createQuietly());

    Livewire::test(EditJobRequisition::class, ['record' => $this->jobRequisition->getKey()])
        ->assertForbidden();
});

it('updates the job requisition priority', function (): void {
    $this->recruiter->user->givePermissionTo('update_job_requisitions');

    Livewire::test(EditJobRequisition::class, ['record' => $this->jobRequisition->getKey()])
        ->fillForm(['priority' => RequisitionPriorityEnum::Urgent])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($this->jobRequisition->refresh()->priority)->toBe(RequisitionPriorityEnum::Urgent);
});

it('allows saving a legacy requisition with null employment_type and work_schedule on edit', function (): void {
    $this->recruiter->user->givePermissionTo('update_job_requisitions');

    $legacy = JobRequisition::factory()
        ->for($this->team)
        ->for($this->department)
        ->for($this->recruiter, 'recruiter')
        ->for($this->recruiter->user, 'createdBy')
        ->create(['employment_type' => null, 'work_schedule' => null]);

    JobPosting::factory()->for($legacy, 'jobRequisition')->create();

    Livewire::test(EditJobRequisition::class, ['record' => $legacy->getKey()])
        ->fillForm(['priority' => RequisitionPriorityEnum::Urgent])
        ->call('save')
        ->assertHasNoFormErrors();
});

it('can soft delete the job requisition via the delete action', function (): void {
    $this->recruiter->user->givePermissionTo('update_job_requisitions');
    $this->recruiter->user->givePermissionTo('delete_job_requisitions');

    Livewire::test(EditJobRequisition::class, ['record' => $this->jobRequisition->getKey()])
        ->callAction(DeleteAction::class)
        ->assertHasNoActionErrors();

    assertSoftDeleted($this->jobRequisition);
});

it('can restore a soft-deleted job requisition via the restore action', function (): void {
    $this->recruiter->user->givePermissionTo('update_job_requisitions');
    $this->recruiter->user->givePermissionTo('restore_job_requisitions');

    $this->jobRequisition->delete();

    Livewire::test(EditJobRequisition::class, ['record' => $this->jobRequisition->getKey()])
        ->callAction(RestoreAction::class)
        ->assertHasNoActionErrors();

    expect($this->jobRequisition->refresh()->deleted_at)->toBeNull();
});
