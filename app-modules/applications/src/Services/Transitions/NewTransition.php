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
        match (ApplicationStatusEnum::tryFrom($meta['to_status'])) {
            ApplicationStatusEnum::InReview => $this->application->update([
                'status' => ApplicationStatusEnum::InReview,
                'current_stage_id' => Stage::query()
                    ->where('job_requisition_id', $this->application->requisition_id)
                    ->where('active', true)
                    ->orderBy('display_order')
                    ->value('id'),
            ]),

            ApplicationStatusEnum::Withdrawn => $this->application->update([
                'status' => ApplicationStatusEnum::Withdrawn,
            ]),

            ApplicationStatusEnum::Rejected => $this->application->update([
                'status' => ApplicationStatusEnum::Rejected,
                'rejected_at' => $meta['rejected_at'] ?? now(),
                'rejected_by' => $meta['by_user_id'] ?? null,
                'rejection_reason_category' => $meta['rejection_reason_category'] ?? null,
                'rejection_reason_details' => $meta['rejection_reason_details'] ?? null,
            ]),

            default => throw new InvalidTransitionException('Transition from New to '.($meta['to_status'] ?? '').' is not allowed'),
        };
    }

    public function notify(array $meta = []): void
    {
        // TODO: adicionar notificações conforme necessário
    }
}
