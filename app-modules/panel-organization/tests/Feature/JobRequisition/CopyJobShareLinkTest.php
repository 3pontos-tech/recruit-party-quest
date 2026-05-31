<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use Filament\Actions\Testing\TestAction;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Actions\CopyJobShareLinkAction;
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

    $this->makeRequisition = fn (array $attributes = []): JobRequisition => JobRequisition::factory()
        ->for($this->team)
        ->for($this->department)
        ->for($this->recruiter, 'recruiter')
        ->for($this->recruiter->user, 'createdBy')
        ->create($attributes);
});

it('builds the candidate detail URL from the job posting slug', function (): void {
    $requisition = ($this->makeRequisition)();
    $posting = JobPosting::factory()->for($requisition, 'jobRequisition')->create();

    $url = CopyJobShareLinkAction::shareUrlFor($requisition->fresh());

    expect($url)
        ->toBeString()
        ->toContain('/vagas/'.$posting->slug)
        ->toStartWith('http');
});

it('returns null when the requisition has no job posting', function (): void {
    $requisition = ($this->makeRequisition)();

    expect(CopyJobShareLinkAction::shareUrlFor($requisition->fresh()))->toBeNull();
});

it('shows the copy share link action enabled when the job has a posting', function (): void {
    $requisition = ($this->makeRequisition)();
    JobPosting::factory()->for($requisition, 'jobRequisition')->create();

    Livewire::test(ListJobRequisitions::class)
        ->assertActionEnabled(TestAction::make('copyShareLink')->table($requisition));
});

it('keeps the copy share link action enabled for internal jobs that have a posting', function (): void {
    $requisition = ($this->makeRequisition)(['is_internal_only' => true]);
    JobPosting::factory()->for($requisition, 'jobRequisition')->create();

    Livewire::test(ListJobRequisitions::class)
        ->assertActionEnabled(TestAction::make('copyShareLink')->table($requisition));
});

it('disables the copy share link action when the job has no posting', function (): void {
    $requisition = ($this->makeRequisition)();

    Livewire::test(ListJobRequisitions::class)
        ->assertActionDisabled(TestAction::make('copyShareLink')->table($requisition));
});
