<?php

declare(strict_types=1);

namespace He4rt\Candidates\Actions;

use He4rt\Candidates\DTOs\Collections\CandidateWorkExperienceCollection;
use He4rt\Candidates\Models\Candidate;

final class StoreCandidateWorkExperiences
{
    public function execute(CandidateWorkExperienceCollection $experiences): void
    {
        /** @var Candidate $candidate */
        $candidate = auth()->user()->candidate;

        foreach ($experiences->jsonSerialize() as $experience) {
            $candidate->workExperiences()->create($experience->jsonSerialize());
        }

    }
}
