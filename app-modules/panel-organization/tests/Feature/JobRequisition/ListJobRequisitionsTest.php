<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages\ListJobRequisitions;
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
});

it('renders the list page successfully', function (): void {
    Livewire::test(ListJobRequisitions::class)
        ->assertOk();
});

it('can see job requisitions belonging to the current tenant', function (): void {
    $jobRequisitions = JobRequisition::factory(3)
        ->for($this->team)
        ->for($this->department)
        ->for($this->recruiter, 'recruiter')
        ->for($this->recruiter->user, 'createdBy')
        ->create();

    Livewire::test(ListJobRequisitions::class)
        ->assertCanSeeTableRecords($jobRequisitions);
});

it('can search job requisitions by post title', function (): void {
    $targetRequisition = JobRequisition::factory()
        ->for($this->team)
        ->for($this->department)
        ->for($this->recruiter, 'recruiter')
        ->for($this->recruiter->user, 'createdBy')
        ->create();

    JobPosting::factory()
        ->for($targetRequisition, 'jobRequisition')
        ->create(['title' => 'Unique PHP Developer Position']);

    $otherRequisition = JobRequisition::factory()
        ->for($this->team)
        ->for($this->department)
        ->for($this->recruiter, 'recruiter')
        ->for($this->recruiter->user, 'createdBy')
        ->create();

    JobPosting::factory()
        ->for($otherRequisition, 'jobRequisition')
        ->create(['title' => 'Backend Engineer Role']);

    Livewire::test(ListJobRequisitions::class)
        ->searchTable('Unique PHP Developer')
        ->assertCanSeeTableRecords([$targetRequisition])
        ->assertCanNotSeeTableRecords([$otherRequisition]);
});
