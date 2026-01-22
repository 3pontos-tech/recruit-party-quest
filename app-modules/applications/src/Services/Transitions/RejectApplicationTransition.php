<?php

declare(strict_types=1);

namespace He4rt\Applications\Services\Transitions;

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
use He4rt\Applications\Exceptions\MissingTransitionDataException;

use function now;

final class RejectApplicationTransition extends AbstractApplicationTransition
{
    public function choices(): array
    {
        return [];
    }

    public function canChange(): bool
    {
        return false;
    }

    public function validate(TransitionData $data): void
    {
        match (true) {
            ! $data->rejectionReasonCategory instanceof RejectionReasonCategoryEnum => throw MissingTransitionDataException::forField('rejection_reason_category'),
            default => null,
        };
    }

    public function processStep(TransitionData $data): void
    {
        $this->application->update([
            'status' => ApplicationStatusEnum::Rejected,
            'rejected_at' => $data->rejectedAt ?? now(),
            'rejected_by' => $data->byUserId,
            'rejection_reason_category' => $data->rejectionReasonCategory?->value,
            'rejection_reason_details' => $data->rejectionReasonDetails,
        ]);
    }

    public function notify(TransitionData $data): void {}
}
