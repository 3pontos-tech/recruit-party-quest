<?php

declare(strict_types=1);

namespace He4rt\Applications\Services\Transitions;

use He4rt\Applications\Enums\ApplicationStatusEnum;

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
        $this->application->status = ApplicationStatusEnum::OfferExtended;
        $this->application->offer_extended_at = $meta['offer_extended_at'] ?? now();
        $this->application->offer_extended_by = $meta['by_user_id'] ?? null;
        $this->application->offer_amount = $meta['offer_amount'] ?? $this->application->offer_amount;
        $this->application->offer_response_deadline = $meta['offer_response_deadline'] ?? $this->application->offer_response_deadline;
        $this->application->save();

        // persist stage history if provided
        if (isset($meta['to_stage_id']) || isset($meta['from_stage_id'])) {
            $this->application->stageHistory()->create([
                'from_stage_id' => $meta['from_stage_id'] ?? null,
                'to_stage_id' => $meta['to_stage_id'] ?? $this->application->current_stage_id,
                'moved_by' => $meta['by_user_id'] ?? null,
                'notes' => $meta['notes'] ?? null,
            ]);
        }
    }

    public function notify(array $meta = []): void
    {
        // handler-specific notifications (emails, webhooks, etc.)
    }
}
