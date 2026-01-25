<?php

declare(strict_types=1);

namespace He4rt\Applications\Actions;

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\CandidateSourceEnum;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;

class ApplyToJobRequisitionAction
{
    /**
     * Apply a candidate to a job requisition.
     *
     * Creates a new Application record and sets it to the first pipeline stage.
     */
    public function execute(
        JobRequisition $requisition,
        Candidate $candidate,
        CandidateSourceEnum $source = CandidateSourceEnum::CareerPage,
    ): Application {
        $application = Application::query()->create([
            'requisition_id' => $requisition->id,
            'candidate_id' => $candidate->id,
            'team_id' => $requisition->team_id,
            'status' => ApplicationStatusEnum::New,
            'source' => $source,
        ]);

        $application->update([
            'current_stage_id' => $application->first_stage->getKey(),
        ]);

        return $application;
    }

    /**
     * Check if a candidate has already applied to a requisition.
     */
    public function hasApplied(JobRequisition $requisition, Candidate $candidate): bool
    {
        return $requisition->applications()
            ->where('candidate_id', $candidate->id)
            ->exists();
    }
}
