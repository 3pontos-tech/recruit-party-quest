<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Candidates\Models\WorkExperience;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Pages\ViewApplication;
use He4rt\Permissions\Roles;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->candidate = Candidate::factory()->create();
    $this->application = Application::factory()
        ->for($this->candidate, 'candidate')
        ->create();
    JobPosting::factory()->for($this->application->requisition, 'jobRequisition')->createOne();
    $this->team = $this->application->team;
    $this->recruiter = Recruiter::factory()->for($this->team, 'team')->create();
    $this->recruiter->user->assignRole(Roles::SuperAdmin->value);
    actingAs($this->recruiter->user);

    filament()->setCurrentPanel(FilamentPanel::Organization->value);
    filament()->setTenant($this->team);
});

it('renders the work experience tab for a past job without an end date', function (): void {
    WorkExperience::factory()
        ->for($this->candidate, 'candidate')
        ->create([
            'is_currently_working_here' => false,
            'end_date' => null,
        ]);

    livewire(ViewApplication::class, ['record' => $this->application->getKey()])
        ->assertOk();
});
