<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Feedback\Models\Evaluation;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Pages\ViewApplication;
use He4rt\Permissions\Roles;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Users\User;

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

it('shows the feedbacks tab with empty state when application has no evaluations', function (): void {
    livewire(ViewApplication::class, ['record' => $this->application->getKey()])
        ->assertOk()
        ->assertSee(__('panel-organization::view.tabs.feedbacks.empty_title'));
});

it('displays evaluations grouped by stage', function (): void {
    $evaluation = Evaluation::factory()
        ->for($this->application, 'application')
        ->for($this->team, 'team')
        ->create();

    livewire(ViewApplication::class, ['record' => $this->application->getKey()])
        ->assertOk()
        ->assertSee($evaluation->evaluator->name)
        ->assertSee($evaluation->overall_rating->getLabel())
        ->assertSee($evaluation->stage->name);
});

it('shows multiple evaluations across different stages', function (): void {
    $evaluationOne = Evaluation::factory()
        ->for($this->application, 'application')
        ->for($this->team, 'team')
        ->create();

    $evaluationTwo = Evaluation::factory()
        ->for($this->application, 'application')
        ->for($this->team, 'team')
        ->create();

    livewire(ViewApplication::class, ['record' => $this->application->getKey()])
        ->assertOk()
        ->assertSee($evaluationOne->evaluator->name)
        ->assertSee($evaluationTwo->evaluator->name);
});

it('denies access to regular users without panel permissions', function (): void {
    actingAs(User::factory()->createQuietly());

    livewire(ViewApplication::class, ['record' => $this->application->getKey()])
        ->assertForbidden();
});
