<?php

declare(strict_types=1);

namespace He4rt\Applications\Services\Transitions;

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Exceptions\MissingTransitionDataException;

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

    public function processStep(array $meta = []): void
    {
        throw_if(blank($meta['rejection_reason_category']), MissingTransitionDataException::class, 'Rejection reason category is required to reject an application');

        $fromStage = $this->application->current_stage_id;

        $this->application->status = ApplicationStatusEnum::Rejected;
        $this->application->rejected_at = $meta['rejected_at'] ?? now();
        $this->application->rejected_by = $meta['by_user_id'] ?? null;
        $this->application->rejection_reason_category = $meta['rejection_reason_category'];
        $this->application->rejection_reason_details = $meta['rejection_reason_details'] ?? null;
        $this->application->save();

        // persist stage history
        $this->application->stageHistory()->create([
            'from_stage_id' => $fromStage,
            'to_stage_id' => $this->application->current_stage_id,
            'moved_by' => $meta['by_user_id'] ?? null,
            'notes' => $meta['notes'] ?? null,
        ]);
    }

    public function notify(array $meta = []): void
    {
        // handler-specific notifications (emails, webhooks, etc.)
        // The general ApplicationStatusChanged event is now dispatched by AbstractApplicationTransition::handle().
    }
}
