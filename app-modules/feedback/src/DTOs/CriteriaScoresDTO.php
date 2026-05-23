<?php

declare(strict_types=1);

namespace He4rt\Feedback\DTOs;

use JsonSerializable;

final readonly class CriteriaScoresDTO implements JsonSerializable
{
    public function __construct(
        public int $technicalSkills,
        public int $communication,
        public int $problemSolving,
        public int $cultureFit,
    ) {}

    /**
     * @param array{
     *   technical_skills: int|string,
     *   communication: int|string,
     *   problem_solving: int|string,
     *   culture_fit: int|string
     * } $data
     */
    public static function make(array $data): self
    {
        return new self(
            technicalSkills: (int) $data['technical_skills'],
            communication: (int) $data['communication'],
            problemSolving: (int) $data['problem_solving'],
            cultureFit: (int) $data['culture_fit']
        );
    }

    /**
     * @return array<string, int>
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
