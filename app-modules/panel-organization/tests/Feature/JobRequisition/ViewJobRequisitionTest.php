<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages\ViewJobRequisition;
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
        ->create();

    $this->jobPosting = JobPosting::factory()
        ->for($this->jobRequisition, 'jobRequisition')
        ->create();
});

it('renders the view page successfully', function (): void {
    Livewire::test(ViewJobRequisition::class, ['record' => $this->jobRequisition->getKey()])
        ->assertOk();
});

it('displays the job posting title in the infolist', function (): void {
    Livewire::test(ViewJobRequisition::class, ['record' => $this->jobRequisition->getKey()])
        ->assertOk()
        ->assertSee($this->jobPosting->title);
});

it('displays the recruiter name in the infolist', function (): void {
    Livewire::test(ViewJobRequisition::class, ['record' => $this->jobRequisition->getKey()])
        ->assertOk()
        ->assertSee($this->recruiter->user->name);
});

it('allows any authenticated user to view a job requisition', function (): void {
    // The view() and viewAny() policies return true for any authenticated user.
    // A recruiter from a different team can still view this requisition.
    $otherRecruiter = Recruiter::factory()->createOne();
    actingAs($otherRecruiter->user);
    filament()->setTenant($this->team);

    Livewire::test(ViewJobRequisition::class, ['record' => $this->jobRequisition->getKey()])
        ->assertOk();
});
