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
        public CarbonImmutable|Carbon $startDate,
        public Carbon|CarbonImmutable|null $endDate = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function make(array $data): self
    {
        return new self(
            companyName: $data['company_name'],
            description: $data['description'],
            isCurrentlyWorking: $data['is_currently_working_here'] ?? false,
            startDate: Date::make($data['start_date']),
            endDate: (filled($data['end_date']) && $data['end_date'] !== 'null')
                ? Date::parse($data['end_date'])
                : null,
        );
    }

    /**
     * @return array{company_name: string, description: string, start_date: null|string, end_date: null|string, is_currently_working_here: bool}
     */
    public function jsonSerialize(): array
    {
        return [
            'company_name' => $this->companyName,
            'description' => $this->description,
            'start_date' => $this->startDate->format('Y-m-d'),
            'end_date' => $this->endDate?->format('Y-m-d'),
            'is_currently_working_here' => $this->isCurrentlyWorking,
        ];
    }
}
