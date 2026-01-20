<?php

declare(strict_types=1);

namespace He4rt\Candidates\DTOs;

use JsonSerializable;

final class CandidateOnboardingDTO implements JsonSerializable
{
    /**
     * @param  array<int|CandidateEducationDTO>  $education
     * @param  array<int|CandidateWorkExperienceDTO>  $work
     */
    public function __construct(
        public array $education,
        public array $work,
    ) {}

    /**
     * @param  array{education: array<int, CandidateEducationDTO>, work: array<int, CandidateWorkExperienceDTO>}  $data
     */
    public static function make(array $data): self
    {
        return new self(
            education: $data['education'],
            work: $data['work'],
        );
    }

    /**
     * @return array{education: array<int, CandidateEducationDTO>, work_experiences: array<int, CandidateWorkExperienceDTO>}
     */
    public function jsonSerialize(): array
    {
        return [
            'education' => $this->education,
            'work_experiences' => $this->work,
        ];
    }
}
