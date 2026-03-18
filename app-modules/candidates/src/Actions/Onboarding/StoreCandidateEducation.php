<?php

declare(strict_types=1);

namespace He4rt\Candidates\Actions\Onboarding;

use He4rt\Candidates\DTOs\Collections\CandidateEducationCollection;
use He4rt\Candidates\Models\Candidate;

final class StoreCandidateEducation
{
    public function execute(CandidateEducationCollection $degree): void
    {
        /** @var Candidate $candidate */
        $candidate = auth()->user()->candidate;

        foreach ($degree->jsonSerialize() as $education) {
            $exists = $candidate->degrees()
                ->where('institution', $education->institution)
                ->where('degree', $education->degree)
                ->where('field_of_study', $education->fieldOfStudy)
                ->exists();

            if (! $exists) {
                $candidate->degrees()->create($education->jsonSerialize());
            }
        }

    }
}
