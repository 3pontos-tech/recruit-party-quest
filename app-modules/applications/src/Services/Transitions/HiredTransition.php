<?php

declare(strict_types=1);

namespace He4rt\Applications\Services\Transitions;

use He4rt\Applications\Enums\ApplicationStatusEnum;

final class HiredTransition extends AbstractApplicationTransition
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
        $this->application->status = ApplicationStatusEnum::Hired;
        $this->application->save();
    }

    public function notify(array $meta = []): void
    {
        // handler-specific notifications (emails, webhooks, etc.)
    }
}
