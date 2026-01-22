<?php

declare(strict_types=1);

namespace He4rt\Applications\Services\Transitions;

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
use He4rt\Applications\Exceptions\InvalidTransitionException;
use He4rt\Applications\Exceptions\MissingTransitionDataException;

final class InReviewTransition extends AbstractApplicationTransition
{
    public function choices(): array
    {
        return [
            ApplicationStatusEnum::InProgress->value => ApplicationStatusEnum::InProgress->getLabel(),
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
            ! in_array($data->toStatus->value, array_keys($this->choices()), true) => throw InvalidTransitionException::notAllowed($data->toStatus),
            $data->isRejection() && ! $data->rejectionReasonCategory instanceof RejectionReasonCategoryEnum => throw MissingTransitionDataException::forField('rejection_reason_category'),
            default => null,
        };
    }

    public function processStep(TransitionData $data): void
    {
        $payload = match ($data->toStatus) {
            ApplicationStatusEnum::InProgress => [
                'status' => ApplicationStatusEnum::InProgress,
                'current_stage_id' => $data->toStageId ?? $this->application->getNextStage()?->getKey(),
            ],
            ApplicationStatusEnum::Rejected => [
                'status' => ApplicationStatusEnum::Rejected,
                'rejected_at' => $data->rejectedAt ?? now(),
                'rejected_by' => $data->byUserId,
                'rejection_reason_category' => $data->rejectionReasonCategory?->value,
                'rejection_reason_details' => $data->rejectionReasonDetails,
            ],
            ApplicationStatusEnum::Withdrawn => [
                'status' => ApplicationStatusEnum::Withdrawn,
            ],
            default => [],
        };

        $this->application->update($payload);
    }

    public function notify(TransitionData $data): void {}
}
