<?php

declare(strict_types=1);

namespace He4rt\Applications\Services\Transitions;

use Carbon\CarbonInterface;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;

final readonly class TransitionData
{
    public function __construct(
        public ApplicationStatusEnum $toStatus,
        public ?string $toStageId,
        public ?bool $advanceStage,
        public ?RejectionReasonCategoryEnum $rejectionReasonCategory,
        public ?string $rejectionReasonDetails,
        public ?CarbonInterface $rejectedAt,
        public ?CarbonInterface $offerExtendedAt,
        public ?float $offerAmount,
        public ?CarbonInterface $offerResponseDeadline,
        public ?string $notes,
        public string $byUserId,
    ) {}

    /**
     * Create a DTO from an associative array.
     *
     * @param  array{
     *     to_status?: ApplicationStatusEnum|string|null,
     *     status?: ApplicationStatusEnum|string|null,
     *     to_stage_id?: string|null,
     *     advance_stage?: bool|null,
     *     rejection_reason_category?: RejectionReasonCategoryEnum|string|null,
     *     rejection_reason_details?: string|null,
     *     rejected_at?: CarbonInterface|string|null,
     *     offer_extended_at?: CarbonInterface|string|null,
     *     offer_amount?: float|int|string|null,
     *     offer_response_deadline?: CarbonInterface|string|null,
     *     notes?: string|null,
     * } $data
     */
    public static function fromArray(array $data, string $byUserId): self
    {
        $toStatus = $data['to_status'] ?? $data['status'] ?? null;

        return new self(
            toStatus: $toStatus,
            toStageId: $data['to_stage_id'] ?? null,
            advanceStage: $data['advance_stage'] ?? null,
            rejectionReasonCategory: isset($data['rejection_reason_category'])
                ? RejectionReasonCategoryEnum::tryFrom($data['rejection_reason_category'])
                : null,
            rejectionReasonDetails: $data['rejection_reason_details'] ?? null,
            rejectedAt: isset($data['rejected_at']) && is_string($data['rejected_at'])
                ? now()->parse($data['rejected_at'])
                : ($data['rejected_at'] ?? null),
            offerExtendedAt: isset($data['offer_extended_at']) && is_string($data['offer_extended_at'])
                ? now()->parse($data['offer_extended_at'])
                : ($data['offer_extended_at'] ?? null),
            offerAmount: isset($data['offer_amount']) ? (float) $data['offer_amount'] : null,
            offerResponseDeadline: isset($data['offer_response_deadline']) && is_string($data['offer_response_deadline'])
                ? now()->parse($data['offer_response_deadline'])
                : ($data['offer_response_deadline'] ?? null),
            notes: $data['notes'] ?? null,
            byUserId: $byUserId,
        );
    }

    /**
     * @return array{
     *     to_status: string,
     *     to_stage_id: string|null,
     *     advance_stage: bool|null,
     *     rejection_reason_category: string|null,
     *     rejection_reason_details: string|null,
     *     rejected_at: string|null,
     *     offer_extended_at: string|null,
     *     offer_amount: float|null,
     *     offer_response_deadline: string|null,
     *     notes: string|null,
     *     by_user_id: string,
     * }
     */
    public function toArray(): array
    {
        return [
            'to_status' => $this->toStatus->value,
            'to_stage_id' => $this->toStageId,
            'advance_stage' => $this->advanceStage,
            'rejection_reason_category' => $this->rejectionReasonCategory?->value,
            'rejection_reason_details' => $this->rejectionReasonDetails,
            'rejected_at' => $this->rejectedAt?->toDateTimeString(),
            'offer_extended_at' => $this->offerExtendedAt?->toDateTimeString(),
            'offer_amount' => $this->offerAmount,
            'offer_response_deadline' => $this->offerResponseDeadline?->toDateTimeString(),
            'notes' => $this->notes,
            'by_user_id' => $this->byUserId,
        ];
    }

    public function isStatusChange(): bool
    {
        return $this->toStatus instanceof ApplicationStatusEnum;
    }

    public function isStageOnlyChange(): bool
    {
        return $this->toStageId !== null || $this->advanceStage === true;
    }

    public function isRejection(): bool
    {
        return $this->toStatus === ApplicationStatusEnum::Rejected;
    }

    public function isOfferExtension(): bool
    {
        return $this->toStatus === ApplicationStatusEnum::OfferExtended;
    }

    public function isWithdrawal(): bool
    {
        return $this->toStatus === ApplicationStatusEnum::Withdrawn;
    }
}
