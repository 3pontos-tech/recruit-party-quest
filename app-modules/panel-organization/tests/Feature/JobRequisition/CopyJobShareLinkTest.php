<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Actions\CopyJobShareLinkAction;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Teams\Department;

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
