<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Models\ApplicationView;
use He4rt\Candidates\Models\Candidate;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Pages\ViewApplication;
use He4rt\Permissions\Roles;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->requisition = JobRequisition::factory()->create();
    JobPosting::factory()->for($this->requisition, 'jobRequisition')->createOne();
    $this->team = $this->requisition->team;
    $this->recruiter = Recruiter::factory()->for($this->team, 'team')->create();
    $this->recruiter->user->assignRole(Roles::SuperAdmin->value);

    $this->application = Application::factory()
        ->recycle($this->team)
        ->for($this->requisition, 'requisition')
        ->for(Candidate::factory()->create(), 'candidate')
        ->create();

    actingAs($this->recruiter->user);

    filament()->setCurrentPanel(FilamentPanel::Organization->value);
    filament()->setTenant($this->team);
});

it('records the team view when an authorized recruiter opens the application', function (): void {
    actingAs($this->recruiter->user);

    livewire(ViewApplication::class, ['record' => $this->application->getKey()])->assertOk();
    livewire(ViewApplication::class, ['record' => $this->application->getKey()])->assertOk();

    assertDatabaseCount(ApplicationView::class, 1);
    expect(ApplicationView::query()->sole()->viewed_by)->toBe($this->recruiter->user->getKey());
});

it('does not record a view when the user is not authorized', function (): void {
    actingAs(User::factory()->createQuietly());

    livewire(ViewApplication::class, ['record' => $this->application->getKey()])->assertForbidden();

    assertDatabaseCount(ApplicationView::class, 0);
});
