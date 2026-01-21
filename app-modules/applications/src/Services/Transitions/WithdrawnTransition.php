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

    public function validate(TransitionData $data): void {}

    public function processStep(TransitionData $data): void
    {
        $this->application->update([
            'status' => ApplicationStatusEnum::Withdrawn,
        ]);
    }

    public function notify(TransitionData $data): void {}
}
