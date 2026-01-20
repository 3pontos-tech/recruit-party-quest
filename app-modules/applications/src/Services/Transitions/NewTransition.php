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

    /**
     * @param  array<string,mixed>  $meta
     */
    public function processStep(array $meta = []): void
    {
        /** @var ApplicationStatusEnum $status */
        $status = $meta['status'];
        match ($status) {
            ApplicationStatusEnum::InReview => $this->forwardToReview(),
            ApplicationStatusEnum::Withdrawn => $this->forwardToWithdrawn(),
            ApplicationStatusEnum::Rejected => $this->rejectApplication($meta),
            default => throw InvalidTransitionException::notAllowed($status),
        };
    }

    public function notify(array $meta = []): void
    {
        // TODO: adicionar notificações conforme necessário
    }

    public function forwardToReview(): bool
    {
        $payload = ['status' => ApplicationStatusEnum::InReview];

        $application = $this->application;
        $nextStage = $application->getNextStage();

        if ($nextStage instanceof Stage) {
            $payload['current_stage_id'] = $nextStage->getKey();
        }

        return $application->update($payload);
    }

    public function forwardToWithdrawn(): bool
    {
        return $this->application->update([
            'status' => ApplicationStatusEnum::Withdrawn,
        ]);
    }

    public function rejectApplication(array $meta): bool
    {
        return $this->application->update([
            'status' => ApplicationStatusEnum::Rejected,
            'rejected_at' => $meta['rejected_at'] ?? now(),
            'rejected_by' => $meta['by_user_id'] ?? null,
            'rejection_reason_category' => $meta['rejection_reason_category'] ?? null,
            'rejection_reason_details' => $meta['rejection_reason_details'] ?? null,
        ]);
    }
}
