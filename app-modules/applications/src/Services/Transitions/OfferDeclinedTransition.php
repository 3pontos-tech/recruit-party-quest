<?php

declare(strict_types=1);

namespace He4rt\Applications\Services\Transitions;

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Events\ApplicationStatusChanged;

final class OfferDeclinedTransition extends AbstractApplicationTransition
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
        $this->application->status = ApplicationStatusEnum::OfferDeclined;
        $this->application->offer_declined_at = $meta['offer_declined_at'] ?? now();
        $this->application->offer_declined_by = $meta['by_user_id'] ?? auth()?->id();
        $this->application->save();
    }

    public function notify(array $meta = []): void
    {
        event(new ApplicationStatusChanged(
            $this->application,
            ApplicationStatusEnum::OfferExtended->value,
            ApplicationStatusEnum::OfferDeclined->value,
            auth()->user() ?? null,
            $meta
        ));
    }
}
