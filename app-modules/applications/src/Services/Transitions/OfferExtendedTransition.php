<?php

declare(strict_types=1);

namespace He4rt\Applications\Services\Transitions;

use He4rt\Applications\Enums\ApplicationStatusEnum;

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

    public function processStep(array $meta = []): void
    {
        $fromStage = $this->application->current_stage_id;

        $this->application->update([
            'status' => ApplicationStatusEnum::OfferExtended,
            'offer_extended_at' => $meta['offer_extended_at'] ?? now(),
            'offer_extended_by' => $meta['by_user_id'] ?? null,
            'offer_amount' => $meta['offer_amount'] ?? $this->application->offer_amount,
            'offer_response_deadline' => $meta['offer_response_deadline'] ?? $this->application->offer_response_deadline,
        ]);

        // persist stage history
        $this->application->stageHistory()->create([
            'from_stage_id' => $fromStage,
            'to_stage_id' => $meta['to_stage_id'] ?? $this->application->current_stage_id,
            'moved_by' => $meta['by_user_id'] ?? null,
            'notes' => $meta['notes'] ?? null,
        ]);
    }

    public function notify(array $meta = []): void
    {
        // handler-specific notifications (emails, webhooks, etc.)
    }
}
