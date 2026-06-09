<?php

declare(strict_types=1);

namespace He4rt\Applications\Services\Transitions;

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Exceptions\InvalidTransitionException;

final class OfferExtendedTransition extends AbstractApplicationTransition
{
    public function choices(): array
    {
        return [
            ApplicationStatusEnum::OfferAccepted->value => ApplicationStatusEnum::OfferAccepted->getLabel(),
            ApplicationStatusEnum::OfferDeclined->value => ApplicationStatusEnum::OfferDeclined->getLabel(),
            ApplicationStatusEnum::Withdrawn->value => ApplicationStatusEnum::Withdrawn->getLabel(),
        ];
    }

    public function canChange(): bool
    {
        return true;
    }

    public function validate(TransitionData $data): void
    {
        if (! in_array($data->toStatus->value, array_keys($this->choices()), true)) {
            throw InvalidTransitionException::notAllowed($data->toStatus);
        }
    }

    public function processStep(TransitionData $data): void
    {
        $this->application->update(['status' => $data->toStatus]);
    }

    public function notify(TransitionData $data): void {}
}
