<?php

declare(strict_types=1);

namespace He4rt\Candidates\DTOs;

use JsonSerializable;

final class CandidateOnboardingDTO implements JsonSerializable
{
    /**
     * @param  array<int|CandidateEducationDTO>  $education
     * @param  array<int|CandidateWorkExperienceDTO>  $work_experiences
     */
    public function __construct(
        public array $education,
        public array $work_experiences,
    ) {}

    /**
     * @param  array{education: array<int, CandidateEducationDTO>, work_experiences: array<int, CandidateWorkExperienceDTO>}  $data
     */
    public static function make(array $data): self
    {
        return new self(
            education: $data['education'],
            work_experiences: $data['work_experiences'],
        );
    }

    /**
     * @return array{education: array<int, CandidateEducationDTO>, work_experiences: array<int, CandidateWorkExperienceDTO>}
     */
    public function jsonSerialize(): array
    {
        return [
            'education' => $this->education,
            'work_experiences' => $this->work_experiences,
        ];
    }
}
