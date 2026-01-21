<?php

declare(strict_types=1);

namespace He4rt\Applications\Services\Transitions;

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Exceptions\InvalidTransitionException;
use He4rt\Recruitment\Stages\Models\Stage;

final class NewTransition extends AbstractApplicationTransition
{
    public function choices(): array
    {
        return [
            ApplicationStatusEnum::InReview->value => ApplicationStatusEnum::InReview->getLabel(),
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
            default => null,
        };
    }

    public function processStep(TransitionData $data): void
    {
        match ($data->toStatus) {
            ApplicationStatusEnum::InReview => $this->forwardToReview($data),
            ApplicationStatusEnum::Withdrawn => $this->forwardToWithdrawn($data),
            ApplicationStatusEnum::Rejected => $this->rejectApplication($data),
            default => throw InvalidTransitionException::notAllowed($data->toStatus),
        };
    }

    public function notify(TransitionData $data): void {}

    public function forwardToReview(TransitionData $data): bool
    {
        $payload = ['status' => ApplicationStatusEnum::InReview];
        $nextStage = $this->application->getNextStage();

        match (true) {
            $nextStage instanceof Stage => $payload['current_stage_id'] = $nextStage->getKey(),
            default => null,
        };

        return $this->application->update($payload);
    }

    public function forwardToWithdrawn(TransitionData $data): bool
    {
        return $this->application->update([
            'status' => ApplicationStatusEnum::Withdrawn,
        ]);
    }

    public function rejectApplication(TransitionData $data): bool
    {
        return $this->application->update([
            'status' => ApplicationStatusEnum::Rejected,
            'rejected_at' => $data->rejectedAt ?? now(),
            'rejected_by' => $data->byUserId,
            'rejection_reason_category' => $data->rejectionReasonCategory?->value,
            'rejection_reason_details' => $data->rejectionReasonDetails,
        ]);
    }
}
