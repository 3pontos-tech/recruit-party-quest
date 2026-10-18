<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Applications\Models\Application;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Pages\ListApplications;
use He4rt\Organization\Livewire\Applications\ApplicationsWorkspace;
use He4rt\Permissions\Roles;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->recruiter = Recruiter::factory()->create();
    $this->recruiter->user->assignRole(Roles::SuperAdmin->value);
    actingAs($this->recruiter->user);

    $this->team = $this->recruiter->team;

    filament()->setCurrentPanel(FilamentPanel::Organization->value);
    filament()->setTenant($this->team);
});

it('renders the workspace instead of the table', function (): void {
    livewire(ListApplications::class)
        ->assertOk()
        ->assertSeeLivewire(ApplicationsWorkspace::class)
        ->assertSee(__('panel-organization::workspace.overview.title'));
});

it('renders without error when a requisition has no stages', function (): void {
    $requisition = JobRequisition::factory()->recycle($this->team)->create(['status' => RequisitionStatusEnum::Published]);
    $requisition->stages()->delete();
    Application::factory()->recycle($this->team)->for($requisition, 'requisition')->withoutCurrentStage()->create();

    livewire(ApplicationsWorkspace::class)
        ->call('openRequisition', $requisition->getKey())
        ->assertOk();
});

it('shows only published requisitions by default', function (): void {
    $published = JobRequisition::factory()->recycle($this->team)->create(['status' => RequisitionStatusEnum::Published]);
    JobPosting::factory()->for($published, 'jobRequisition')->createOne(['title' => 'PUBLISHED_VACANCY_OPTION']);
    $draft = JobRequisition::factory()->recycle($this->team)->create(['status' => RequisitionStatusEnum::Draft]);
    JobPosting::factory()->for($draft, 'jobRequisition')->createOne(['title' => 'DRAFT_VACANCY_OPTION']);

    livewire(ListApplications::class)
        ->assertSee('PUBLISHED_VACANCY_OPTION')
        ->assertDontSee('DRAFT_VACANCY_OPTION');
});
