<?php

declare(strict_types=1);

namespace He4rt\Applications\Services\Transitions;

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
use He4rt\Applications\Exceptions\InvalidTransitionException;
use He4rt\Applications\Exceptions\MissingTransitionDataException;
use He4rt\Recruitment\Stages\Models\Stage;

final class InProgressTransition extends AbstractApplicationTransition
{
    public function choices(): array
    {
        return [
            ApplicationStatusEnum::OfferExtended->value => ApplicationStatusEnum::OfferExtended->getLabel(),
            ApplicationStatusEnum::Rejected->value => ApplicationStatusEnum::Rejected->getLabel(),
            ApplicationStatusEnum::Withdrawn->value => ApplicationStatusEnum::Withdrawn->getLabel(),
        ];
    }

    public function canChange(): bool
    {
        return true;
    }

    public function validate(TransitionData $data): void
    {
        match (true) {
            ! $data->isStatusChange() && ! $data->isStageOnlyChange() => throw new InvalidTransitionException('Must provide to_status or to_stage_id/advance_stage'),

            $data->isStatusChange() && ! in_array($data->toStatus->value, array_keys($this->choices()), true) => throw InvalidTransitionException::notAllowed($data->toStatus),

            $data->isRejection() && ! $data->rejectionReasonCategory instanceof RejectionReasonCategoryEnum => throw MissingTransitionDataException::forField('rejection_reason_category'),

            default => null,
        };
    }

    public function processStep(TransitionData $data): void
    {
        match (true) {
            $data->isOfferExtension() => $this->processOfferExtension($data),
            $data->isRejection() => $this->processRejection($data),
            $data->isWithdrawal() => $this->processWithdrawal(),
            $data->isStageOnlyChange() => $this->processStageMove($data),
            default => throw new InvalidTransitionException('Invalid transition parameters'),
        };
    }

    public function notify(TransitionData $data): void {}

    private function processOfferExtension(TransitionData $data): void
    {
        $this->application->update([
            'status' => ApplicationStatusEnum::OfferExtended,
            'offer_extended_at' => $data->offerExtendedAt ?? now(),
            'offer_extended_by' => $data->byUserId,
            'offer_amount' => $data->offerAmount ?? $this->application->offer_amount,
            'offer_response_deadline' => $data->offerResponseDeadline ?? $this->application->offer_response_deadline,
        ]);
    }

    private function processRejection(TransitionData $data): void
    {
        $this->application->update([
            'status' => ApplicationStatusEnum::Rejected,
            'rejected_at' => $data->rejectedAt ?? now(),
            'rejected_by' => $data->byUserId,
            'rejection_reason_category' => $data->rejectionReasonCategory?->value,
            'rejection_reason_details' => $data->rejectionReasonDetails,
        ]);
    }

    private function processWithdrawal(): void
    {
        $this->application->update(['status' => ApplicationStatusEnum::Withdrawn]);
    }

    private function processStageMove(TransitionData $data): void
    {
        $toStageId = $this->resolveTargetStage($data);

        throw_unless($toStageId, InvalidTransitionException::class, 'No valid target stage found');

        $this->application->update([
            'current_stage_id' => $toStageId,
        ]);
    }

    private function resolveTargetStage(TransitionData $data): ?string
    {
        return match (true) {
            $data->toStageId !== null => $data->toStageId,
            $data->advanceStage === true => Stage::query()
                ->where('job_requisition_id', $this->application->requisition_id)
                ->where('active', true)
                ->where('display_order', '>', $this->application->currentStage->display_order ?? -1)
                ->orderBy('display_order')
                ->value('id'),
            default => null,
        };
    }
}
