<?php

declare(strict_types=1);

namespace He4rt\Candidates\DTOs;

use JsonSerializable;

final class CandidateOnboardingDTO implements JsonSerializable
{
    /**
     * @param  array<int, CandidateEducationDTO>  $education
     * @param  array<int, CandidateWorkExperienceDTO>  $work_experiences
     */
    public function __construct(
        public array $education,
        public array $work_experiences,
    ) {}

    /**
     * Hydrates the DTO from raw arrays — the shape produced by jsonSerialize(),
     * which is how the payload arrives after the broadcast round-trip (Echo → Livewire).
     *
     * @param  array{education?: array<int, array<string, mixed>>, work_experiences?: array<int, array<string, mixed>>}  $data
     */
    public static function make(array $data): self
    {
        return new self(
            education: array_map(CandidateEducationDTO::make(...), $data['education'] ?? []),
            work_experiences: array_map(CandidateWorkExperienceDTO::make(...), $data['work_experiences'] ?? []),
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
