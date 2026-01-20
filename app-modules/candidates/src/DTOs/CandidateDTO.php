<?php

declare(strict_types=1);

namespace He4rt\Candidates\DTOs;

use Carbon\CarbonImmutable;

final readonly class CandidateDTO
{
    /**
     * @param  array<int,mixed>  $contactLinks
     */
    public function __construct(
        public string $userID,
        public ?string $phoneNumber,
        public ?string $headline,
        public ?string $summary,
        public ?CarbonImmutable $availabilityDate,
        public bool $willingToRelocate,
        public bool $is_open_to_remote,
        public ?float $expectedSalary,
        public string $expectedSalaryCurrency,
        public ?string $linkedin_url,
        public ?string $portfolio_url,
        public ?string $experienceLevel,
        public ?array $contactLinks,
        public ?string $selfIdentifiedGender,
        public ?string $source,
        public bool $isOnboarded,
        public ?CarbonImmutable $onboardingCompletedAt,
        public ?string $timezone,
        public string $preferredLanguage,
        public bool $dataConsentGiven,
    ) {}
}
