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
        $this->application->status = ApplicationStatusEnum::Withdrawn;

        $now = now();

        // record withdrawn metadata if provided
        $this->application->withdrawn_at = $meta['withdrawn_at'] ?? $now;
        $this->application->withdrawn_by = $meta['withdrawn_by'] ?? $meta['by_user_id'] ?? null;
        $this->application->withdrawn_reason = $meta['withdrawn_reason'] ?? null;

        $this->application->save();
    }

    public function notify(array $meta = []): void
    {
        // handler-specific notifications (emails, webhooks, etc.)
    }
}
