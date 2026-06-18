<?php

declare(strict_types=1);

namespace He4rt\Applications\Actions;

use He4rt\Applications\DTOs\ApplicationDTO;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\CandidateSourceEnum;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Services\Applications\StoreApplication;
use He4rt\Candidates\Models\Candidate;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;

readonly class ApplyToJobRequisitionAction
{
    public function __construct(private StoreApplication $storeApplication) {}

    /**
     * Apply a candidate to a job requisition.
     *
     * Delegates creation to StoreApplication (single creation chokepoint that
     * dispatches ApplicationSubmitted).
     */
    public function execute(
        JobRequisition $requisition,
        Candidate $candidate,
        CandidateSourceEnum $source = CandidateSourceEnum::CareerPage,
    ): Application {
        return $this->storeApplication->execute(new ApplicationDTO(
            requisitionId: $requisition->id,
            candidateId: $candidate->id,
            teamId: $requisition->team_id,
            status: ApplicationStatusEnum::New,
            source: $source,
        ));
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
