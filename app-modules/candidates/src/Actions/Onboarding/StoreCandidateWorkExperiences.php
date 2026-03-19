<?php

declare(strict_types=1);

namespace He4rt\Candidates\Actions\Onboarding;

use He4rt\Candidates\DTOs\Collections\CandidateWorkExperienceCollection;
use He4rt\Candidates\Models\Candidate;

final class StoreCandidateWorkExperiences
{
    public function execute(CandidateWorkExperienceCollection $experiences): void
    {
        /** @var Candidate $candidate */
        $candidate = auth()->user()->candidate;

        foreach ($experiences->jsonSerialize() as $experience) {
            $payload = $experience->jsonSerialize();

            $candidate->workExperiences()->firstOrCreate(
                [
                    'company_name' => $experience->companyName,
                    'start_date' => ($experience->startDate ?? now())->startOfDay(),
                ],
                $payload,
            );
        }

    }
}
