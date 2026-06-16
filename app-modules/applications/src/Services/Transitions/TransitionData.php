<?php

declare(strict_types=1);

namespace He4rt\Applications\Services\Transitions;

use Carbon\CarbonInterface;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
use InvalidArgumentException;

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
        public ?string $byUserId,
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
    public static function fromArray(array $data, ?string $byUserId = null): self
    {
        $toStatus = $data['to_status'] ?? $data['status'] ?? null;

        throw_if($toStatus === null, InvalidArgumentException::class, 'TransitionData requires a "to_status" or "status".');

        $rejectionCategory = $data['rejection_reason_category'] ?? null;

        return new self(
            toStatus: $toStatus instanceof ApplicationStatusEnum
                ? $toStatus
                : ApplicationStatusEnum::from($toStatus),
            toStageId: $data['to_stage_id'] ?? null,
            advanceStage: $data['advance_stage'] ?? null,
            rejectionReasonCategory: $rejectionCategory instanceof RejectionReasonCategoryEnum
                ? $rejectionCategory
                : (is_string($rejectionCategory) ? RejectionReasonCategoryEnum::tryFrom($rejectionCategory) : null),
            rejectionReasonDetails: $data['rejection_reason_details'] ?? null,
            rejectedAt: self::parseDate($data['rejected_at'] ?? null),
            offerExtendedAt: self::parseDate($data['offer_extended_at'] ?? null),
            offerAmount: isset($data['offer_amount']) ? (float) $data['offer_amount'] : null,
            offerResponseDeadline: self::parseDate($data['offer_response_deadline'] ?? null),
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
     *     by_user_id: string|null,
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

    private static function parseDate(CarbonInterface|string|null $value): ?CarbonInterface
    {
        if ($value === null) {
            return null;
        }

        return $value instanceof CarbonInterface ? $value : now()->parse($value);
    }
}
