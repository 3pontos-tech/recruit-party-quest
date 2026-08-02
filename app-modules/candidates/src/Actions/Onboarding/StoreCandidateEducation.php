<?php

declare(strict_types=1);

namespace He4rt\Candidates\Actions\Onboarding;

use He4rt\Candidates\Actions\EnsureCandidateProfile;
use He4rt\Candidates\DTOs\Collections\CandidateEducationCollection;

final class StoreCandidateEducation
{
    public function execute(CandidateEducationCollection $degree): void
    {
        $candidate = resolve(EnsureCandidateProfile::class)->execute(auth()->user());

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
