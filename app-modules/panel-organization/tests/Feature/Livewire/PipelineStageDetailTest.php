<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Models\ApplicationStageHistory;
use He4rt\Candidates\Models\Candidate;
use He4rt\Feedback\Models\Evaluation;
use He4rt\Organization\Livewire\PipelineStageDetail;
use He4rt\Permissions\Roles;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Recruitment\Stages\Enums\StageTypeEnum;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Teams\Team;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    $this->candidate = Candidate::factory()->create();
    $this->application = Application::factory()
        ->for($this->candidate, 'candidate')
        ->create();
    $this->team = $this->application->team;
    $this->recruiter = Recruiter::factory()->for($this->team, 'team')->create();
    $this->recruiter->user->assignRole(Roles::SuperAdmin->value);
    actingAs($this->recruiter->user);

    filament()->setCurrentPanel(FilamentPanel::Organization->value);
    filament()->setTenant($this->team);

    // ApplicationFactory::configure() may attach a random stage to current_stage_id.
    // Force it to the first stage so tests start from a deterministic state.
    $firstStage = $this->application
        ->requisition
        ->stages()
        ->where('active', true)
        ->orderBy('display_order')
        ->first();

    if ($firstStage !== null) {
        $this->application->update(['current_stage_id' => $firstStage->id]);
        $this->application->refresh();
    }

    $this->stages = $this->application
        ->requisition
        ->stages()
        ->where('active', true)
        ->orderBy('display_order')
        ->get();
});

it('mounts with current_stage_id from application', function (): void {
    Livewire::test(PipelineStageDetail::class, ['application' => $this->application])
        ->assertOk()
        ->assertSet('applicationId', $this->application->id)
        ->assertSet('currentStageId', $this->application->current_stage_id);
});

it('does not navigate to a future (not yet reached) stage', function (): void {
    // beforeEach leaves current at the first stage, so the rest are in the future
    $futureStage = $this->stages->get(1);

    Livewire::test(PipelineStageDetail::class, ['application' => $this->application->fresh()])
        ->call('goToStage', $futureStage->id)
        ->assertSet('currentStageId', $this->application->current_stage_id);
});

it('navigates to the next reached stage', function (): void {
    expect($this->stages->count())->toBeGreaterThan(1);

    // application advanced to the last stage, so every stage is reached
    $last = $this->stages->last();
    $this->application->update(['current_stage_id' => $last->id]);

    $first = $this->stages->first();
    $second = $this->stages->get(1);

    Livewire::test(PipelineStageDetail::class, ['application' => $this->application->fresh()])
        ->call('goToStage', $first->id)
        ->call('goToNextStage')
        ->assertSet('currentStageId', $second->id);
});

it('navigates to the previous stage in display_order', function (): void {
    expect($this->stages->count())->toBeGreaterThan(1);

    $first = $this->stages->first();
    $second = $this->stages->get(1);

    $this->application->update(['current_stage_id' => $second->id]);

    Livewire::test(PipelineStageDetail::class, ['application' => $this->application->fresh()])
        ->call('goToPreviousStage')
        ->assertSet('currentStageId', $first->id);
});

it('does not move when on the first stage and previous is called', function (): void {
    $first = $this->stages->first();
    $this->application->update(['current_stage_id' => $first->id]);

    Livewire::test(PipelineStageDetail::class, ['application' => $this->application->fresh()])
        ->call('goToPreviousStage')
        ->assertSet('currentStageId', $first->id);
});

it('does not move when on the last stage and next is called', function (): void {
    $last = $this->stages->last();
    $this->application->update(['current_stage_id' => $last->id]);

    Livewire::test(PipelineStageDetail::class, ['application' => $this->application->fresh()])
        ->call('goToNextStage')
        ->assertSet('currentStageId', $last->id);
});

it('ignores goToStage when target stage does not belong to the pipeline', function (): void {
    Livewire::test(PipelineStageDetail::class, ['application' => $this->application])
        ->call('goToStage', 'non-existent-stage-id')
        ->assertSet('currentStageId', $this->application->current_stage_id);
});

it('excludes inactive stages from navigation', function (): void {
    $first = $this->stages->first();
    $first->update(['active' => false]);

    Livewire::test(PipelineStageDetail::class, ['application' => $this->application->fresh()])
        ->call('goToStage', $first->id)
        ->assertSet('currentStageId', $this->application->fresh()->current_stage_id);
});

it('allows navigating to a reached hidden stage for staff users', function (): void {
    $hiddenStage = Stage::factory()
        ->for($this->application->requisition, 'requisition')
        ->state([
            'team_id' => $this->team->id,
            'stage_type' => StageTypeEnum::HiddenStage,
            'hidden' => true,
            'active' => true,
            'display_order' => 999,
        ])
        ->create();

    // application sits on the hidden stage, so it (and everything before) is reached
    $this->application->update(['current_stage_id' => $hiddenStage->id]);

    Livewire::test(PipelineStageDetail::class, ['application' => $this->application->fresh()])
        ->call('goToStage', $this->stages->first()->id)
        ->assertSet('currentStageId', $this->stages->first()->id)
        ->call('goToStage', $hiddenStage->id)
        ->assertSet('currentStageId', $hiddenStage->id);
});

it('renders the stepper with the journey stages', function (): void {
    Livewire::test(PipelineStageDetail::class, ['application' => $this->application->fresh()])
        ->assertOk()
        ->assertSee(__('panel-organization::view.pipeline.stage_detail.stepper_title'))
        ->assertSee($this->stages->first()->name)
        ->assertSee($this->stages->last()->name);
});

it('marks the rejection point and shows the banner when the application is rejected', function (): void {
    $stoppedAt = $this->stages->get(2);

    $this->application->update([
        'current_stage_id' => $stoppedAt->id,
        'status' => ApplicationStatusEnum::Rejected,
        'rejection_reason_category' => RejectionReasonCategoryEnum::Qualifications,
    ]);

    Livewire::test(PipelineStageDetail::class, ['application' => $this->application->fresh()])
        ->assertOk()
        ->assertSee(ApplicationStatusEnum::Rejected->getLabel())
        ->assertSee(RejectionReasonCategoryEnum::Qualifications->getLabel());
});

it('marks the withdrawal point when the candidate withdrew', function (): void {
    $stoppedAt = $this->stages->get(2);

    $this->application->update([
        'current_stage_id' => $stoppedAt->id,
        'status' => ApplicationStatusEnum::Withdrawn,
    ]);

    Livewire::test(PipelineStageDetail::class, ['application' => $this->application->fresh()])
        ->assertOk()
        ->assertSee(ApplicationStatusEnum::Withdrawn->getLabel());
});

it('marks a declined offer as a terminal point', function (): void {
    $stoppedAt = $this->stages->get(4);

    $this->application->update([
        'current_stage_id' => $stoppedAt->id,
        'status' => ApplicationStatusEnum::OfferDeclined,
    ]);

    Livewire::test(PipelineStageDetail::class, ['application' => $this->application->fresh()])
        ->assertOk()
        ->assertSee(ApplicationStatusEnum::OfferDeclined->getLabel());
});

it('treats the current stage as reached for a fresh application without history', function (): void {
    // beforeEach leaves the application on the first stage with no stage history
    $first = $this->stages->first();

    Livewire::test(PipelineStageDetail::class, ['application' => $this->application->fresh()])
        ->assertOk()
        ->call('goToStage', $first->id)
        ->assertSet('currentStageId', $first->id)
        ->assertDontSee(__('panel-organization::view.pipeline.stage_detail.empty_future'));
});

it('refreshes the footer navigation after a single goToNextStage click', function (): void {
    // application stopped at Offer (index 4): Interview is reached, Hired is future
    $interview = $this->stages->get(3);
    $offer = $this->stages->get(4);
    $this->application->update([
        'current_stage_id' => $offer->id,
        'status' => ApplicationStatusEnum::OfferExtended,
    ]);

    Livewire::test(PipelineStageDetail::class, ['application' => $this->application->fresh()])
        ->call('goToStage', $interview->id)
        ->call('goToNextStage')
        ->assertSet('currentStageId', $offer->id)
        // Offer is the current stage, so there is no reached "next" — the footer
        // "next" button must disappear after ONE click (no stale computed cache).
        ->assertDontSee(__('panel-organization::view.pipeline.stage_detail.next'));
});

it('shows movedBy user name in timeline for the current stage', function (): void {
    $first = $this->stages->first();
    $second = $this->stages->get(1);

    $this->application->update(['current_stage_id' => $second->id]);

    ApplicationStageHistory::factory()
        ->for($this->application, 'application')
        ->state([
            'team_id' => $this->team->id,
            'from_stage_id' => $first->id,
            'to_stage_id' => $second->id,
            'moved_by' => $this->recruiter->user->getKey(),
            'notes' => 'Approved at screening',
        ])
        ->create();

    Livewire::test(PipelineStageDetail::class, ['application' => $this->application->fresh()])
        ->assertSee($this->recruiter->user->name)
        ->assertSee('Approved at screening');
});

it('does not show timeline entries from other stages', function (): void {
    $first = $this->stages->first();
    $second = $this->stages->get(1);

    $this->application->update(['current_stage_id' => $first->id]);

    ApplicationStageHistory::factory()
        ->for($this->application, 'application')
        ->state([
            'team_id' => $this->team->id,
            'from_stage_id' => $first->id,
            'to_stage_id' => $second->id,
            'moved_by' => $this->recruiter->user->getKey(),
            'notes' => 'Should not show here',
        ])
        ->create();

    Livewire::test(PipelineStageDetail::class, ['application' => $this->application->fresh()])
        ->assertDontSee('Should not show here');
});

it('returns an empty stages collection when application has no requisition', function (): void {
    $this->application->requisition()->update(['deleted_at' => now()]);
    $this->application->update(['current_stage_id' => null]);

    Livewire::test(PipelineStageDetail::class, ['application' => $this->application->fresh()])
        ->assertOk()
        ->assertSet('currentStageId', '');
});

it('returns false for isFutureStage when stage was already reached', function (): void {
    $first = $this->stages->first();
    $second = $this->stages->get(1);

    $this->application->update(['current_stage_id' => $second->id]);

    ApplicationStageHistory::factory()
        ->for($this->application, 'application')
        ->state([
            'team_id' => $this->team->id,
            'from_stage_id' => $first->id,
            'to_stage_id' => $second->id,
            'moved_by' => $this->recruiter->user->getKey(),
        ])
        ->create();

    Livewire::test(PipelineStageDetail::class, ['application' => $this->application->fresh()])
        ->assertDontSee(__('panel-organization::view.pipeline.stage_detail.empty_future'));
});

it('renders empty state when timeline is empty for the current stage', function (): void {
    $first = $this->stages->first();
    $this->application->update(['current_stage_id' => $first->id]);

    Livewire::test(PipelineStageDetail::class, ['application' => $this->application->fresh()])
        ->assertSee(__('panel-organization::view.pipeline.stage_detail.empty_notes'));
});

it('renders empty state when no interviewers are assigned', function (): void {
    Livewire::test(PipelineStageDetail::class, ['application' => $this->application->fresh()])
        ->assertSee(__('panel-organization::view.pipeline.stage_detail.empty_interviewers'));
});

it('renders empty state when no evaluations exist for the current stage', function (): void {
    Livewire::test(PipelineStageDetail::class, ['application' => $this->application->fresh()])
        ->assertSee(__('panel-organization::view.pipeline.stage_detail.empty_evaluations'));
});

it('renders timeline entry even when movedBy is null (system-moved)', function (): void {
    $first = $this->stages->first();
    $second = $this->stages->get(1);

    $this->application->update(['current_stage_id' => $second->id]);

    ApplicationStageHistory::factory()
        ->for($this->application, 'application')
        ->state([
            'team_id' => $this->team->id,
            'from_stage_id' => $first->id,
            'to_stage_id' => $second->id,
            'moved_by' => null,
            'notes' => 'Auto-advanced by system rule',
        ])
        ->create();

    Livewire::test(PipelineStageDetail::class, ['application' => $this->application->fresh()])
        ->assertOk()
        ->assertSee('Auto-advanced by system rule');
});

it('handles application without current stage gracefully', function (): void {
    $this->application->update(['current_stage_id' => null]);

    Livewire::test(PipelineStageDetail::class, ['application' => $this->application->fresh()])
        ->assertOk()
        ->assertSet('currentStageId', '');
});

it('blocks access for applications from a different tenant', function (): void {
    $otherTeam = Team::factory()->create();
    $otherCandidate = Candidate::factory()->create();
    $otherApplication = Application::factory()
        ->for($otherCandidate, 'candidate')
        ->create(['team_id' => $otherTeam->id]);

    Livewire::test(PipelineStageDetail::class, ['application' => $otherApplication->fresh()])
        ->assertStatus(403);
});

it('renders multiple evaluations on the same stage', function (): void {
    $first = $this->stages->first();
    $this->application->update(['current_stage_id' => $first->id]);

    Evaluation::factory()
        ->for($this->application, 'application')
        ->state([
            'team_id' => $this->team->id,
            'stage_id' => $first->id,
            'evaluator_id' => $this->recruiter->user->getKey(),
            'notes' => 'First reviewer feedback',
        ])
        ->create();

    Evaluation::factory()
        ->for($this->application, 'application')
        ->state([
            'team_id' => $this->team->id,
            'stage_id' => $first->id,
            'evaluator_id' => $this->recruiter->user->getKey(),
            'notes' => 'Second reviewer feedback',
        ])
        ->create();

    Livewire::test(PipelineStageDetail::class, ['application' => $this->application->fresh()])
        ->assertOk()
        ->assertSee('First reviewer feedback')
        ->assertSee('Second reviewer feedback');
});

it('renders timeline entry when from_stage_id is null (initial application)', function (): void {
    $first = $this->stages->first();
    $this->application->update(['current_stage_id' => $first->id]);

    ApplicationStageHistory::factory()
        ->for($this->application, 'application')
        ->state([
            'team_id' => $this->team->id,
            'from_stage_id' => null,
            'to_stage_id' => $first->id,
            'moved_by' => $this->recruiter->user->getKey(),
            'notes' => 'Initial candidate submission',
        ])
        ->create();

    Livewire::test(PipelineStageDetail::class, ['application' => $this->application->fresh()])
        ->assertOk()
        ->assertSee('Initial candidate submission');
});
