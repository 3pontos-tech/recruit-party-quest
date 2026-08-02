<?php

declare(strict_types=1);

namespace He4rt\Candidates\Actions\Onboarding;

use He4rt\Candidates\Actions\EnsureCandidateProfile;
use He4rt\Candidates\DTOs\Collections\CandidateWorkExperienceCollection;
use He4rt\Candidates\DTOs\WorkExperienceMetadata;

final class StoreCandidateWorkExperiences
{
    public function execute(CandidateWorkExperienceCollection $experiences): void
    {
        $candidate = resolve(EnsureCandidateProfile::class)->execute(auth()->user());

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
