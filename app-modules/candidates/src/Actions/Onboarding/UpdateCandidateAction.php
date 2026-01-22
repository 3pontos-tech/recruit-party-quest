<?php

declare(strict_types=1);

namespace He4rt\Candidates\Actions\Onboarding;

use He4rt\Candidates\DTOs\CandidateDTO;
use He4rt\Candidates\Models\Candidate;

final class UpdateCandidateAction
{
    public function execute(CandidateDTO $dto): void
    {
        $candidate = Candidate::query()->where('user_id', $dto->userID);
        $candidate->update([
            'phone_number' => $dto->phoneNumber ?? null,
            'headline' => $dto->headline ?? null,
            'summary' => $dto->summary ?? null,
            'expected_salary' => $dto->expectedSalary ?? null,
            'expected_salary_currency' => $dto->expectedSalaryCurrency ?? 'USD',
            'availability_date' => $dto->availabilityDate ?? null,
            'willing_to_relocate' => $dto->willingToRelocate ?? false,
            'is_open_to_remote' => $dto->is_open_to_remote ?? true,
            'experience_level' => $dto->experienceLevel ?? null,
            'timezone' => $dto->timezone ?? 'UTC',
            'preferred_language' => $dto->preferredLanguage ?? 'en',
            'data_consent_given' => true,
            'is_onboarded' => true,
            'onboarding_completed_at' => now(),
        ]);
    }
}
