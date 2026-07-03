<?php

declare(strict_types=1);

namespace He4rt\Applications\Actions;

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\CandidateSourceEnum;
use He4rt\Applications\Events\ApplicationSubmitted;
use He4rt\Applications\Exceptions\RequisitionNotPublishedException;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;

final class ApplyToJobRequisitionAction
{
    /**
     * Apply a candidate to a job requisition.
     *
     * Single creation chokepoint: builds the application, assigns the first
     * stage, and dispatches ApplicationSubmitted exactly once.
     */
    public function execute(
        JobRequisition $requisition,
        Candidate $candidate,
        CandidateSourceEnum $source = CandidateSourceEnum::CareerPage,
    ): Application {
        if (! $requisition->isPublished()) {
            throw RequisitionNotPublishedException::cannotApplyToRequisition($requisition);
        }

        $application = Application::query()->create([
            'requisition_id' => $requisition->getKey(),
            'candidate_id' => $candidate->getKey(),
            'team_id' => $requisition->team_id,
            'status' => ApplicationStatusEnum::New,
            'source' => $source,
        ]);

        $application->update([
            'current_stage_id' => $application->first_stage?->getKey(),
        ]);

        event(new ApplicationSubmitted($application));

        return $application;
    }
}
