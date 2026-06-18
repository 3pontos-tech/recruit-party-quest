<?php

declare(strict_types=1);

use He4rt\Applications\Actions\ApplyToJobRequisitionAction;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Events\ApplicationSubmitted;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\assertDatabaseHas;

it('applies a candidate by delegating to StoreApplication and dispatches the event', function (): void {
    Event::fake([ApplicationSubmitted::class]);

    $candidate = Candidate::factory()->create();
    $requisition = JobRequisition::factory()->create();

    $application = resolve(ApplyToJobRequisitionAction::class)->execute($requisition, $candidate);

    assertDatabaseHas(Application::class, [
        'id' => $application->getKey(),
        'requisition_id' => $requisition->getKey(),
        'candidate_id' => $candidate->getKey(),
        'status' => ApplicationStatusEnum::New->value,
    ]);

    Event::assertDispatched(ApplicationSubmitted::class);
});

it('reports whether a candidate already applied', function (): void {
    Event::fake([ApplicationSubmitted::class]);

    $candidate = Candidate::factory()->create();
    $requisition = JobRequisition::factory()->create();
    $action = resolve(ApplyToJobRequisitionAction::class);

    expect($action->hasApplied($requisition, $candidate))->toBeFalse();

    $action->execute($requisition, $candidate);

    expect($action->hasApplied($requisition, $candidate))->toBeTrue();
});
