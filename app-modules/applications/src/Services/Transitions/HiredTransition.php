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

    public function validate(TransitionData $data): void {}

    public function processStep(TransitionData $data): void
    {
        $this->application->update([
            'status' => ApplicationStatusEnum::Hired,
        ]);
    }

    public function notify(TransitionData $data): void {}
}
