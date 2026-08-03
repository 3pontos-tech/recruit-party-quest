<?php

declare(strict_types=1);

namespace He4rt\Candidates\Actions\Onboarding;

use He4rt\Candidates\DTOs\Collections\CandidateEducationCollection;
use He4rt\Candidates\Models\Candidate;

final class StoreCandidateEducation
{
    public function execute(Candidate $candidate, CandidateEducationCollection $degree): void
    {
        foreach ($degree->jsonSerialize() as $education) {
            $payload = $education->jsonSerialize();

            $candidate->degrees()->firstOrCreate(
                [
                    'institution' => $education->institution,
                    'degree' => $education->degree,
                    'field_of_study' => $education->fieldOfStudy,
                ],
                $payload,
            );
        }

    }
}
