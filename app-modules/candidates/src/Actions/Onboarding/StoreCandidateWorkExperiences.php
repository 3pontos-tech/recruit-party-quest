<?php

declare(strict_types=1);

namespace He4rt\Candidates\Actions\Onboarding;

use He4rt\Candidates\DTOs\Collections\CandidateWorkExperienceCollection;
use He4rt\Candidates\DTOs\WorkExperienceMetadata;
use He4rt\Candidates\Models\Candidate;

final class StoreCandidateWorkExperiences
{
    public function execute(Candidate $candidate, CandidateWorkExperienceCollection $experiences): void
    {
        foreach ($experiences->jsonSerialize() as $experience) {
            if (blank($experience->companyName)) {
                continue;
            }

            $attributes = $experience->jsonSerialize();
            unset($attributes['skills']);

            $candidate->workExperiences()->firstOrCreate(
                [
                    'company_name' => $experience->companyName,
                    'start_date' => ($experience->startDate ?? now())->startOfDay(),
                ],
                [
                    ...$attributes,
                    'metadata' => new WorkExperienceMetadata($experience->skills),
                ],
            );
        }
    }
}
