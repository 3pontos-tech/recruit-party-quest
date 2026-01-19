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
        $fromStage = $this->application->current_stage_id;

        $this->application->update([
            'status' => ApplicationStatusEnum::OfferDeclined,
        ]);

        $this->application->stageHistory()->create([
            'from_stage_id' => $fromStage,
            'to_stage_id' => $this->application->current_stage_id,
            'moved_by' => $meta['by_user_id'] ?? null,
            'notes' => $meta['notes'] ?? null,
        ]);
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
