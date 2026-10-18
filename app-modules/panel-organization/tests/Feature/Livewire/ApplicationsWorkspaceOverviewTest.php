<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Models\ApplicationView;
use He4rt\Candidates\Models\Candidate;
use He4rt\Organization\Livewire\Applications\ApplicationsWorkspace;
use He4rt\Permissions\Roles;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Screening\Models\ScreeningQuestion;
use He4rt\Screening\Models\ScreeningResponse;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->requisition = JobRequisition::factory()->create(['status' => RequisitionStatusEnum::Published]);
    JobPosting::factory()->for($this->requisition, 'jobRequisition')->createOne(['title' => 'Engenharia de Plataforma']);
    $this->team = $this->requisition->team;

    $this->draft = JobRequisition::factory()->recycle($this->team)->create(['status' => RequisitionStatusEnum::Draft]);
    JobPosting::factory()->for($this->draft, 'jobRequisition')->createOne(['title' => 'Vaga Rascunho']);

    $this->plain = JobRequisition::factory()->recycle($this->team)->create(['status' => RequisitionStatusEnum::Published]);
    JobPosting::factory()->for($this->plain, 'jobRequisition')->createOne(['title' => 'Vaga Sem Eliminatória']);

    $recruiter = Recruiter::factory()->for($this->team, 'team')->create();
    $recruiter->user->assignRole(Roles::SuperAdmin->value);
    actingAs($recruiter->user);
    filament()->setCurrentPanel(FilamentPanel::Organization->value);
    filament()->setTenant($this->team);

    $question = ScreeningQuestion::factory()->yesNo()->knockout()->create([
        'team_id' => $this->team->getKey(),
        'screenable_type' => $this->requisition->getMorphClass(),
        'screenable_id' => $this->requisition->getKey(),
    ]);

    $make = fn (JobRequisition $requisition, ApplicationStatusEnum $status): Application => Application::factory()
        ->recycle($this->team)
        ->for($requisition, 'requisition')
        ->for(Candidate::factory()->create(), 'candidate')
        ->create(['status' => $status]);

    $this->passed = $make($this->requisition, ApplicationStatusEnum::New);
    $this->failed = $make($this->requisition, ApplicationStatusEnum::New);
    $this->seen = $make($this->requisition, ApplicationStatusEnum::InProgress);
    ApplicationView::factory()->forApplication($this->seen)->create();

    ScreeningResponse::factory()->yesNoResponse(true)->create(['team_id' => $this->team->getKey(), 'application_id' => $this->passed->getKey(), 'question_id' => $question->getKey()]);
    ScreeningResponse::factory()->yesNoResponse(false)->knockoutFailed()->create(['team_id' => $this->team->getKey(), 'application_id' => $this->failed->getKey(), 'question_id' => $question->getKey()]);
});

it('opens on the overview with only published requisitions and their signals', function (): void {
    livewire(ApplicationsWorkspace::class)
        ->assertOk()
        ->assertSee(__('panel-organization::workspace.overview.title'))
        ->assertSee('Engenharia de Plataforma')
        ->assertSee('Vaga Sem Eliminatória')
        ->assertDontSee('Vaga Rascunho')
        ->assertSee(trans_choice('panel-organization::workspace.overview.knockout_passed', 1, ['count' => 1]))
        ->assertSee(__('panel-organization::workspace.overview.no_knockout'))
        ->assertSee(trans_choice('panel-organization::workspace.overview.unseen', 2, ['count' => 2]));
});

it('includes non published requisitions when asked', function (): void {
    livewire(ApplicationsWorkspace::class)
        ->set('onlyPublished', false)
        ->assertSee('Vaga Rascunho');
});

it('searches requisitions by title', function (): void {
    livewire(ApplicationsWorkspace::class)
        ->set('requisitionSearch', 'plataforma')
        ->assertSee('Engenharia de Plataforma')
        ->assertDontSee('Vaga Sem Eliminatória');
});

it('opens a requisition and shows its header with knockout totals', function (): void {
    livewire(ApplicationsWorkspace::class)
        ->call('openRequisition', $this->requisition->getKey())
        ->assertSet('requisitionId', $this->requisition->getKey())
        ->assertSee(__('panel-organization::workspace.header.knockout_title', ['count' => 1]))
        ->assertSee(trans_choice('panel-organization::workspace.header.knockout_passed', 1, ['count' => 1]))
        ->assertSee(trans_choice('panel-organization::workspace.header.knockout_failed', 1, ['count' => 1]))
        ->assertSee(trans_choice('panel-organization::workspace.header.knockout_unanswered', 1, ['count' => 1]))
        ->call('closeRequisition')
        ->assertSet('requisitionId', '')
        ->assertSee(__('panel-organization::workspace.overview.title'));
});

it('falls back to the overview when the requisition belongs to another team', function (): void {
    $foreign = JobRequisition::factory()->create(['status' => RequisitionStatusEnum::Published]);

    livewire(ApplicationsWorkspace::class, ['requisitionId' => $foreign->getKey()])
        ->assertOk()
        ->assertSet('requisitionId', '')
        ->assertSee(__('panel-organization::workspace.overview.title'));
});

it('falls back to the overview when the job parameter is not a uuid', function (): void {
    livewire(ApplicationsWorkspace::class, ['requisitionId' => 'abc'])
        ->assertOk()
        ->assertSet('requisitionId', '')
        ->assertSee(__('panel-organization::workspace.overview.title'));
});

it('finds requisitions through the switcher search', function (): void {
    livewire(ApplicationsWorkspace::class)
        ->call('openRequisition', $this->requisition->getKey())
        ->set('requisitionSearch', 'Sem Elimin')
        ->assertSee('Vaga Sem Eliminatória');
});
