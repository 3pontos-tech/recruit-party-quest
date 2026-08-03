<?php

declare(strict_types=1);

namespace He4rt\Candidates\DTOs;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use JsonSerializable;

final readonly class CandidateEducationDTO implements JsonSerializable
{
    public function __construct(
        public string $institution,
        public string $degree,
        public string $fieldOfStudy,
        public bool $isEnrolled,
        public CarbonImmutable|Carbon|null $startDate = null,
        public Carbon|CarbonImmutable|null $endDate = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function make(array $data): self
    {
        return new self(
            institution: (string) ($data['institution'] ?? ''),
            degree: (string) ($data['degree'] ?? ''),
            fieldOfStudy: (string) ($data['field_of_study'] ?? ''),
            isEnrolled: (bool) ($data['is_enrolled'] ?? false),
            startDate: ResumeDate::parse($data['start_date'] ?? null),
            endDate: ResumeDate::parse($data['end_date'] ?? null),
        );
    }

    /**
     * @return array{institution: string, degree: string, field_of_study: string, is_enrolled: bool, start_date: string, end_date: string|null}
     */
    public function jsonSerialize(): array
    {
        return [
            'institution' => $this->institution,
            'degree' => $this->degree,
            'field_of_study' => $this->fieldOfStudy,
            'is_enrolled' => $this->isEnrolled,
            'start_date' => ($this->startDate ?? now())->format('Y-m-d'),
            'end_date' => $this->endDate?->format('Y-m-d'),
        ];
    }
}
