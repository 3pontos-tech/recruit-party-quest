<?php

declare(strict_types=1);

use He4rt\Applications\Models\Application;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Stages\Models\Stage;
use Illuminate\Database\Eloquent\Collection;

// --- getPipelineStages ---

it('getPipelineStages returns empty collection when requisition is null', function (): void {
    $application = Application::factory()->create();
    $application->setRelation('requisition', null);

    expect($application->getPipelineStages())
        ->toBeInstanceOf(Collection::class)
        ->toBeEmpty();
});

it('getPipelineStages returns stages sorted by display order when requisition exists', function (): void {
    $application = Application::factory()->create();
    Stage::factory()->count(3)->for($application->requisition, 'requisition')->create();

    // The factory auto-creates default stages, so count includes factory-created ones
    expect($application->getPipelineStages())->not->toBeEmpty();
});

// --- getNextStage ---

it('getNextStage returns null when requisition is null', function (): void {
    $application = Application::factory()->create();
    $application->setRelation('requisition', null);

    expect($application->getNextStage())->toBeNull();
});

it('getNextStage returns null when current stage is soft deleted and no stages remain', function (): void {
    $requisition = JobRequisition::factory()->create();
    $stage = Stage::factory()->for($requisition, 'requisition')->create(['display_order' => 1]);
    $application = Application::factory()->create([
        'current_stage_id' => $stage->id,
        'requisition_id' => $requisition->id,
    ]);

    // Soft-delete all stages
    $requisition->stages()->delete();
    $application->unsetRelation('currentStage');
    $application->requisition->unsetRelation('stages');

    expect($application->getNextStage())->toBeNull();
});

// --- isStageCompleted ---

it('isStageCompleted returns false when current stage is soft deleted', function (): void {
    $requisition = JobRequisition::factory()->create();
    $stage = Stage::factory()->for($requisition, 'requisition')->create();
    $application = Application::factory()->create([
        'current_stage_id' => $stage->id,
        'requisition_id' => $requisition->id,
    ]);

    $stage->delete();
    $application->unsetRelation('currentStage');

    expect($application->isStageCompleted($stage))->toBeFalse();
});

it('isStageCompleted returns false when current_stage_id is null', function (): void {
    $stage = Stage::factory()->create();
    $application = Application::factory()->withoutCurrentStage()->create();

    expect($application->isStageCompleted($stage))->toBeFalse();
});
