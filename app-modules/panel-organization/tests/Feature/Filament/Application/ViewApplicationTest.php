<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Pages\ViewApplication;
use He4rt\Permissions\Roles;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Recruitment\Stages\Enums\StageTypeEnum;
use He4rt\Recruitment\Stages\Models\Stage;
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
    $this->candidate->user->givePermissionTo('view_applications');
    $this->recruiter = Recruiter::factory()->for($this->team, 'team')->create();
    $this->recruiter->user->assignRole(Roles::SuperAdmin->value);
    actingAs($this->recruiter->user);

    filament()->setCurrentPanel(FilamentPanel::Organization->value);
    filament()->setTenant($this->team);

});
it('should render', function (): void {
    livewire(ViewApplication::class, ['record' => $this->application->getKey()])
        ->assertOk();
});

test('only authorized user can see the application', function (): void {
    actingAs(User::factory()->createQuietly());
    livewire(ViewApplication::class, ['record' => $this->application->getKey()])
        ->assertforbidden();
});

test('should see the complete stage pipeline independent if its hidden', function (): void {
    $stages = Stage::factory(5)
        ->for($this->application->requisition, 'requisition')
        ->state(['stage_type' => StageTypeEnum::Screening])
        ->create();

    livewire(ViewApplication::class, ['record' => $this->application->getKey()])
        ->assertOk()
        ->assertSee($stages->pluck('name')->toArray())
        ->assertSee($stages->pluck('description')->toArray());
});
