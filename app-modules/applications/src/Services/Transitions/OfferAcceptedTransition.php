<?php

declare(strict_types=1);

namespace He4rt\Applications\Services\Transitions;

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Events\ApplicationStatusChanged;

final class OfferAcceptedTransition extends AbstractApplicationTransition
{
    public function choices(): array
    {
        return [
            ApplicationStatusEnum::Hired->value => ApplicationStatusEnum::Hired->getLabel(),
        ];
    }

    public function canChange(): bool
    {
        return true;
    }

    public function processStep(array $meta = []): void
    {
        $this->application->update([
            'status' => ApplicationStatusEnum::OfferAccepted,
        ]);
    }

    public function notify(array $meta = []): void
    {
        event(new ApplicationStatusChanged(
            $this->application,
            ApplicationStatusEnum::OfferExtended->value,
            ApplicationStatusEnum::OfferAccepted->value,
            auth()->user() ?? null,
            $meta
        ));
    }
}
