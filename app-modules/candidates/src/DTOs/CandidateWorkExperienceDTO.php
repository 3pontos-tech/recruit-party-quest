<?php

declare(strict_types=1);

namespace He4rt\Candidates\DTOs;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use JsonSerializable;

final readonly class CandidateWorkExperienceDTO implements JsonSerializable
{
    public function __construct(
        public string $companyName,
        public string $description,
        public bool $isCurrentlyWorking,
        public ?string $position = null,
        /** @var list<string> */
        public array $skills = [],
        public CarbonImmutable|Carbon|null $startDate = null,
        public Carbon|CarbonImmutable|null $endDate = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function make(array $data): self
    {
        return new self(
            companyName: (string) ($data['company_name'] ?? ''),
            description: (string) ($data['description'] ?? ''),
            isCurrentlyWorking: (bool) ($data['is_currently_working_here'] ?? false),
            position: filled($data['position'] ?? null) ? (string) $data['position'] : null,
            skills: self::normalizeSkills($data['skills'] ?? []),
            startDate: (filled($data['start_date'] ?? null) && $data['start_date'] !== 'null')
                ? Date::parse($data['start_date'])
                : null,
            endDate: (filled($data['end_date'] ?? null) && $data['end_date'] !== 'null')
                ? Date::parse($data['end_date'])
                : null,
        );
    }

    /**
     * @return array{company_name: string, position: string|null, description: string, skills: list<string>, start_date: string, end_date: null|string, is_currently_working_here: bool}
     */
    public function jsonSerialize(): array
    {
        return [
            'company_name' => $this->companyName,
            'position' => $this->position,
            'description' => $this->description,
            'skills' => $this->skills,
            'start_date' => ($this->startDate ?? now())->format('Y-m-d'),
            'end_date' => $this->endDate?->format('Y-m-d'),
            'is_currently_working_here' => $this->isCurrentlyWorking,
        ];
    }

    /**
     * @return list<string>
     */
    private static function normalizeSkills(mixed $skills): array
    {
        if (! is_array($skills)) {
            return [];
        }

        return array_values(array_filter(
            array_map(fn (mixed $skill): string => is_scalar($skill) ? mb_trim((string) $skill) : '', $skills),
            fn (string $skill): bool => $skill !== '',
        ));
    }
}
