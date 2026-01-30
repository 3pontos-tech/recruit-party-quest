<?php

declare(strict_types=1);

namespace He4rt\Feedback\DTOs;

use JsonSerializable;

final readonly class CriteriaScoresDTO implements JsonSerializable
{
    public function __construct(
        public string $technicalSkills,
        public string $communication,
        public string $problemSolving,
        public string $cultureFit,
    ) {}

    /**
     * @param array{
     *   technical_skills: string,
     *   communication: string,
     *   problem_solving: string,
     *   culture_fit: string
     * } $data
     */
    public static function make(array $data): self
    {
        return new self(
            technicalSkills: $data['technical_skills'],
            communication: $data['communication'],
            problemSolving: $data['problem_solving'],
            cultureFit: $data['culture_fit']
        );
    }

    /**
     * @return array<string, string>
     */
    public function jsonSerialize(): array
    {
        return [
            'technical_skills' => $this->technicalSkills,
            'communication' => $this->communication,
            'problem_solving' => $this->problemSolving,
            'culture_fit' => $this->cultureFit,
        ];
    }
}
