<?php

declare(strict_types=1);

namespace He4rt\Applications\States;

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Exceptions\InvalidTransitionException;

final class OfferExtendedApplicationState extends ApplicationState
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

    /**
     * Post-offer transitions (accepted/declined/withdrawn) are status-only by design.
     *
     * The offer fields (offer_extended_at, offer_extended_by, offer_amount,
     * offer_response_deadline) are persisted earlier, when the offer is created in
     * InProgressApplicationState::processOfferExtension() on the InProgress -> OfferExtended
     * step. This step intentionally updates only the status so it never overwrites
     * those values. Fields are collected in StateTransitionAction and carried by
     * TransitionData.
     */
    public function processStep(TransitionData $data): void
    {
        $this->application->update(['status' => $data->toStatus]);
    }

    public function notify(TransitionData $data): void {}
}
