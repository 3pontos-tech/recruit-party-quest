<?php

declare(strict_types=1);

namespace He4rt\Applications\Services\Transitions;

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Exceptions\InvalidTransitionException;
use He4rt\Recruitment\Stages\Enums\StageTypeEnum;

final class OfferAcceptedTransition extends AbstractApplicationTransition
{
    public function choices(): array
    {
        return [
            ApplicationStatusEnum::Hired->value => ApplicationStatusEnum::Hired->getLabel(),
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
            default => null,
        };
    }

    public function processStep(TransitionData $data): void
    {
        $this->application->update([
            'status' => $data->toStatus,
        ]);

        if ($data->toStatus === ApplicationStatusEnum::Hired) {
            $this->advanceToStageType(StageTypeEnum::Hired);
        }
    }

    public function notify(TransitionData $data): void {}
}
