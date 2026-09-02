<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Applications\Enums\ApplicationListSort;
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

it('lists the candidates of the requisition with knockout verdict and seen state', function (): void {
    livewire(ApplicationsWorkspace::class)
        ->call('openRequisition', $this->requisition->getKey())
        ->assertSee($this->passed->candidate->user->name)
        ->assertSee($this->failed->candidate->user->name)
        ->assertSee(__('panel-organization::workspace.queue.verdict_failed', ['fails' => 1, 'total' => 1]))
        ->assertSee(__('panel-organization::workspace.queue.verdict_passed'))
        ->assertSee(__('panel-organization::workspace.queue.unseen_title'))
        ->assertSee(__('panel-organization::workspace.queue.seen_title'));
});

it('hides knockout signals for a requisition without knockout questions', function (): void {
    $application = Application::factory()->recycle($this->team)->for($this->plain, 'requisition')->for(Candidate::factory()->create(), 'candidate')->create();

    $plainQuestion = ScreeningQuestion::factory()->yesNo()->create([
        'team_id' => $this->team->getKey(),
        'screenable_type' => $this->plain->getMorphClass(),
        'screenable_id' => $this->plain->getKey(),
        'is_knockout' => false,
        'knockout_criteria' => null,
    ]);

    ScreeningResponse::factory()->yesNoResponse(false)->knockoutFailed()->create([
        'team_id' => $this->team->getKey(),
        'application_id' => $application->getKey(),
        'question_id' => $plainQuestion->getKey(),
    ]);

    livewire(ApplicationsWorkspace::class)
        ->call('openRequisition', $this->plain->getKey())
        ->assertSee($application->candidate->user->name)
        ->assertSee(__('panel-organization::workspace.header.no_knockout'))
        ->assertDontSee(__('panel-organization::workspace.queue.verdict_passed'))
        ->assertDontSee(__('panel-organization::workspace.queue.verdict_unanswered'))
        ->assertDontSee('shadow-[inset_3px_0_0_0_var(--color-red-500)]');
});

it('filters by screening verdict, seen state, status group, stage and search', function (): void {
    $firstStage = $this->requisition->stages()->orderBy('display_order')->first();

    livewire(ApplicationsWorkspace::class)
        ->call('openRequisition', $this->requisition->getKey())
        ->call('filterScreening', 'failed')
        ->assertSee($this->failed->candidate->user->name)
        ->assertDontSee($this->passed->candidate->user->name)
        ->call('filterScreening', 'passed')
        ->assertSee($this->passed->candidate->user->name)
        ->assertDontSee($this->failed->candidate->user->name)
        ->call('filterScreening', 'all')
        ->call('filterSeen', 'seen')
        ->assertSee($this->seen->candidate->user->name)
        ->assertDontSee($this->passed->candidate->user->name)
        ->call('filterSeen', 'unseen')
        ->assertSee($this->passed->candidate->user->name)
        ->assertDontSee($this->seen->candidate->user->name)
        ->call('clearFilters')
        ->call('filterStatusGroup', 'active')
        ->assertSee($this->seen->candidate->user->name)
        ->assertDontSee($this->passed->candidate->user->name)
        ->call('clearFilters')
        ->call('filterStage', $firstStage->getKey())
        ->assertSet('stageId', $firstStage->getKey())
        ->call('sortBy', 'name')
        ->assertSet('sort', ApplicationListSort::Name)
        ->set('search', 'zzz-nao-existe')
        ->assertSee(__('panel-organization::workspace.queue.empty'));
});

it('previews the selected candidate without recording a team view', function (): void {
    livewire(ApplicationsWorkspace::class)
        ->call('openRequisition', $this->requisition->getKey())
        ->assertSee(__('panel-organization::workspace.preview.empty'))
        ->call('select', $this->failed->getKey())
        ->assertSet('selectedId', $this->failed->getKey())
        ->assertSee(__('panel-organization::workspace.preview.knockout_failed_title', ['fails' => 1, 'total' => 1]))
        ->assertSee($this->failed->tracking_code)
        ->assertSee(__('panel-organization::workspace.preview.open'));

    expect(ApplicationView::query()->where('application_id', $this->failed->getKey())->exists())->toBeFalse();
});

it('shows who saw the application in the preview', function (): void {
    livewire(ApplicationsWorkspace::class)
        ->call('openRequisition', $this->requisition->getKey())
        ->call('select', $this->seen->getKey())
        ->assertSee($this->seen->teamView->viewer->name);
});
