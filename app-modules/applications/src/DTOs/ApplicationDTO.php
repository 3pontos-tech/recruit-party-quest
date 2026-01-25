<?php

declare(strict_types=1);

namespace He4rt\Applications\DTOs;

use Carbon\CarbonImmutable;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\CandidateSourceEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
use Illuminate\Support\Facades\Date;
use Ramsey\Uuid\Uuid;

final readonly class ApplicationDTO
{
    public function __construct(
        public string|Uuid $applicationId,
        public string $requisitionId,
        public string $candidateId,
        public string $teamId,
        public ApplicationStatusEnum $status,
        public CandidateSourceEnum $source,
        public ?string $source_details = null,
        public ?string $cover_letter = null,
        public ?string $tracking_code = null,
        public ?CarbonImmutable $rejected_at = null,
        public ?string $rejected_by = null,
        public ?RejectionReasonCategoryEnum $rejection_reason_category = null,
        public ?string $rejection_reason_details = null,
        public ?CarbonImmutable $offer_extended_at = null,
        public ?string $offer_extended_by = null,
        public ?float $offer_amount = null,
        public ?CarbonImmutable $offer_response_deadline = null,
    ) {}

    /**
     * @param array{
     *   application_id: string,
     *   requisition_id: string,
     *   candidate_id: string,
     *   team_id: string,
     *   status: string,
     *   source: string,
     *   source_details?: string|null,
     *   cover_letter?: string|null,
     *   tracking_code?: string|null,
     *   rejected_at?: string|null,
     *   rejected_by?: string|null,
     *   rejection_reason_category?: RejectionReasonCategoryEnum|string|null,
     *   rejection_reason_details?: string|null,
     *   offer_extended_at?: string|null,
     *   offer_extended_by?: string|null,
     *   offer_amount?: mixed,
     *   offer_response_deadline?: string|null
     * } $data
     */
    public static function make(array $data): self
    {
        return new self(
            applicationId: $data['application_id'],
            requisitionId: $data['requisition_id'],
            candidateId: $data['candidate_id'],
            teamId: $data['team_id'],
            status: ApplicationStatusEnum::from($data['status']),
            source: CandidateSourceEnum::from($data['source']),
            source_details: $data['source_details'] ?? null,
            cover_letter: $data['cover_letter'] ?? null,
            tracking_code: $data['tracking_code'] ?? null,

            rejected_at: isset($data['rejected_at'])
                ? Date::parse($data['rejected_at'])
                : null,

            rejected_by: $data['rejected_by'] ?? null,

            rejection_reason_category: isset($data['rejection_reason_category']) ? RejectionReasonCategoryEnum::from($data['rejection_reason_category']) : null,
            rejection_reason_details: $data['rejection_reason_details'] ?? null,

            offer_extended_at: isset($data['offer_extended_at'])
                ? Date::parse($data['offer_extended_at'])
                : null,

            offer_extended_by: $data['offer_extended_by'] ?? null,
            offer_amount: $data['offer_amount'] ?? null,

            offer_response_deadline: isset($data['offer_response_deadline'])
                ? Date::parse($data['offer_response_deadline'])
                : null,
        );
    }
}
