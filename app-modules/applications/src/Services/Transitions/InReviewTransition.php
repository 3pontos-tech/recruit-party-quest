<?php

declare(strict_types=1);

namespace He4rt\Applications\Services\Transitions;

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Exceptions\InvalidTransitionException;

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

    public function processStep(array $meta = []): void
    {
        $this->application->update([
            'status' => ApplicationStatusEnum::InReview,
        ]);

        if (isset($meta['to_status'])) {
            match (ApplicationStatusEnum::from($meta['to_status'])) {
                ApplicationStatusEnum::InProgress => $this->application->update([
                    'status' => ApplicationStatusEnum::InProgress,
                    'current_stage_id' => $meta['to_stage_id'] ?? $this->application->current_stage_id,
                ]),

                ApplicationStatusEnum::Rejected => $this->application->update([
                    'status' => ApplicationStatusEnum::Rejected,
                    'rejected_at' => $meta['rejected_at'] ?? now(),
                    'rejected_by' => $meta['by_user_id'] ?? null,
                    'rejection_reason_category' => $meta['rejection_reason_category'] ?? null,
                    'rejection_reason_details' => $meta['rejection_reason_details'] ?? null,
                ]),

                ApplicationStatusEnum::Withdrawn => $this->application->update([
                    'status' => ApplicationStatusEnum::Withdrawn,
                ]),

                default => throw new InvalidTransitionException('Transition from New to '.($to->value ?? '').' is not allowed'),
            };

            if (isset($meta['to_stage_id'])) {
                $this->application->stageHistory()->create([
                    'from_stage_id' => $meta['from_stage_id'] ?? null,
                    'to_stage_id' => $meta['to_stage_id'],
                    'moved_by' => $meta['by_user_id'] ?? null,
                    'notes' => $meta['notes'] ?? null,
                ]);
            }
        }
    }

    public function notify(array $meta = []): void
    {
        // TODO: adicionar notificações conforme necessário
    }
}
