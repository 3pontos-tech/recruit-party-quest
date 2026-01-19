<?php

declare(strict_types=1);

namespace He4rt\Applications\Services\Transitions;

use He4rt\Applications\Enums\ApplicationStatusEnum;

/**
 * Handle candidate-initiated or admin-initiated withdrawals.
 */
final class WithdrawnTransition extends AbstractApplicationTransition
{
    /**
     * @return array<string,string|null>
     */
    public function choices(): array
    {
        return [];
    }

    public function canChange(): bool
    {
        return false;
    }

    /**
     * @param  array<string,mixed>  $meta
     */
    public function processStep(array $meta = []): void
    {
        $fromStage = $this->application->current_stage_id;

        $this->application->update([
            'status' => ApplicationStatusEnum::Withdrawn,
        ]);

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
    }
}
