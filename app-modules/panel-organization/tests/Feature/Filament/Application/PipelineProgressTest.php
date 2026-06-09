<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Models\Application;
use He4rt\Permissions\Roles;
use He4rt\Recruitment\Stages\Enums\StageTypeEnum;
use He4rt\Teams\Team;
use He4rt\Users\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    filament()->setCurrentPanel(FilamentPanel::Organization->value);

    $this->admin = User::factory()->create();
    $this->admin->assignRole(Roles::Admin->value);
    actingAs($this->admin);

    $this->team = Team::factory()->create(['owner_id' => $this->admin->id]);

    filament()->setTenant($this->team);
});

function renderPipelineProgress(Application $application): string
{
    return Blade::render(
        '<x-applications::sidebar.pipeline-progress :record="$record" />',
        ['record' => $application->fresh()->load('requisition.stages', 'currentStage')],
    );
}

it('renders the hired stage as a completed outcome, not an active one', function (): void {
    $application = Application::factory()
        ->withStatus(ApplicationStatusEnum::Hired)
        ->create(['team_id' => $this->team->id]);

    $hiredStage = $application->requisition->stages()
        ->where('stage_type', StageTypeEnum::Hired)->firstOrFail();
    $application->update(['current_stage_id' => $hiredStage->id]);

    $html = renderPipelineProgress($application);

    $hiredOnPrefix = Str::before(__('panel-organization::view.pipeline.hired_on'), ':date');
    $activeSincePrefix = Str::before(__('panel-organization::view.pipeline.active_since'), ':date');

    expect($html)->toContain($hiredOnPrefix)
        ->and($html)->toContain(__('panel-organization::view.pipeline.hired'))
        ->and($html)->not->toContain($activeSincePrefix);
});

it('still shows an active (not hired) marker for an in-progress application', function (): void {
    $application = Application::factory()
        ->withStatus(ApplicationStatusEnum::OfferExtended)
        ->create(['team_id' => $this->team->id]);

    $offerStage = $application->requisition->stages()
        ->where('stage_type', StageTypeEnum::Offer)->firstOrFail();
    $application->update(['current_stage_id' => $offerStage->id]);

    $html = renderPipelineProgress($application);

    $hiredOnPrefix = Str::before(__('panel-organization::view.pipeline.hired_on'), ':date');
    $activeSincePrefix = Str::before(__('panel-organization::view.pipeline.active_since'), ':date');

    expect($html)->toContain($activeSincePrefix)
        ->and($html)->not->toContain($hiredOnPrefix);
});

it('marks the current stage as a terminal stop for a withdrawn application', function (): void {
    $application = Application::factory()
        ->withStatus(ApplicationStatusEnum::Withdrawn)
        ->create(['team_id' => $this->team->id]);

    $interviewStage = $application->requisition->stages()
        ->where('stage_type', StageTypeEnum::Interview)->firstOrFail();
    $application->update(['current_stage_id' => $interviewStage->id]);

    $html = renderPipelineProgress($application);

    $withdrawnOnPrefix = Str::before(__('panel-organization::view.pipeline.withdrawn_on'), ':date');
    $activeSincePrefix = Str::before(__('panel-organization::view.pipeline.active_since'), ':date');

    // The organization keeps the timeline but the exit stage reads as a terminal
    // stop (status label + withdrawal date), never as an ongoing "active" stage.
    expect($html)->toContain(ApplicationStatusEnum::Withdrawn->getLabel())
        ->and($html)->toContain($withdrawnOnPrefix)
        ->and($html)->not->toContain($activeSincePrefix);
});

it('marks the current stage as a terminal stop for a declined offer', function (): void {
    $application = Application::factory()
        ->withStatus(ApplicationStatusEnum::OfferDeclined)
        ->create(['team_id' => $this->team->id]);

    $offerStage = $application->requisition->stages()
        ->where('stage_type', StageTypeEnum::Offer)->firstOrFail();
    $application->update(['current_stage_id' => $offerStage->id]);

    $html = renderPipelineProgress($application);

    $declinedOnPrefix = Str::before(__('panel-organization::view.pipeline.declined_on'), ':date');
    $activeSincePrefix = Str::before(__('panel-organization::view.pipeline.active_since'), ':date');

    expect($html)->toContain(ApplicationStatusEnum::OfferDeclined->getLabel())
        ->and($html)->toContain($declinedOnPrefix)
        ->and($html)->not->toContain($activeSincePrefix);
});
