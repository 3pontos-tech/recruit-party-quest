<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Applications\Models\Application;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Pages\ListApplications;
use He4rt\Permissions\Roles;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->recruiter = Recruiter::factory()->create();
    $this->recruiter->user->assignRole(Roles::SuperAdmin->value);
    actingAs($this->recruiter->user);

    $this->application = Application::factory()->create();
    $this->team = $this->application->team;
    JobPosting::factory()->for($this->application->requisition, 'jobRequisition')->create();

    filament()->setCurrentPanel(FilamentPanel::Organization->value);
    filament()->setTenant($this->team);
});

it('renders applications table without error when requisition has no stages', function (): void {
    $this->application->requisition->stages()->delete();

    livewire(ListApplications::class)
        ->assertOk();
});

it('renders applications table without error when application has null current stage', function (): void {
    $this->application->update(['current_stage_id' => null]);

    livewire(ListApplications::class)
        ->assertOk();
});
