<?php

declare(strict_types=1);

namespace He4rt\Applications\Services\Transitions;

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Exceptions\InvalidTransitionException;
use He4rt\Applications\Exceptions\MissingTransitionDataException;

use function now;

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
        match (true) {
            ! in_array($data->toStatus->value, array_keys($this->choices()), true) => throw InvalidTransitionException::notAllowed($data->toStatus),
            $data->toStatus === ApplicationStatusEnum::OfferAccepted && $data->toStageId === null => throw MissingTransitionDataException::forField('to_stage_id'),
            default => null,
        };
    }

    public function processStep(TransitionData $data): void
    {
        $this->application->update([
            'status' => ApplicationStatusEnum::OfferExtended,
            'offer_extended_at' => $data->offerExtendedAt ?? now(),
            'offer_extended_by' => $data->byUserId,
            'offer_amount' => $data->offerAmount ?? $this->application->offer_amount,
            'offer_response_deadline' => $data->offerResponseDeadline ?? $this->application->offer_response_deadline,
        ]);
    }

    public function notify(TransitionData $data): void {}
}
