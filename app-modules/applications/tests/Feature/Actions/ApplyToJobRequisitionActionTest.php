<?php

declare(strict_types=1);

use He4rt\Applications\Actions\ApplyToJobRequisitionAction;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\CandidateSourceEnum;
use He4rt\Applications\Events\ApplicationSubmitted;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\assertDatabaseHas;

it('creates an application and dispatches ApplicationSubmitted', function (): void {
    Event::fake([ApplicationSubmitted::class]);

    $candidate = Candidate::factory()->create();
    $requisition = JobRequisition::factory()->create();

    $application = resolve(ApplyToJobRequisitionAction::class)
        ->execute($requisition, $candidate, CandidateSourceEnum::LinkedIn);

    assertDatabaseHas(Application::class, [
        'id' => $application->getKey(),
        'requisition_id' => $requisition->getKey(),
        'candidate_id' => $candidate->getKey(),
        'status' => ApplicationStatusEnum::New->value,
        'source' => CandidateSourceEnum::LinkedIn->value,
    ]);

    Event::assertDispatched(fn (ApplicationSubmitted $event): bool => $event->application->is($application));
});
