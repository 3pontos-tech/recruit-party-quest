<?php

declare(strict_types=1);

namespace He4rt\Applications\Services\Transitions;

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Recruitment\Stages\Models\Stage;

final class InProgressTransition extends AbstractApplicationTransition
{
    public function choices(): array
    {
        return [
            ApplicationStatusEnum::InProgress->value => ApplicationStatusEnum::InProgress->getLabel(),
            ApplicationStatusEnum::OfferExtended->value => ApplicationStatusEnum::OfferExtended->getLabel(),
            ApplicationStatusEnum::Rejected->value => ApplicationStatusEnum::Rejected->getLabel(),
            ApplicationStatusEnum::Withdrawn->value => ApplicationStatusEnum::Withdrawn->getLabel(),
        ];
    }

    public function canChange(): bool
    {
        return true;
    }

    public function processStep(array $meta = []): void
    {
        //        $this->application->stageHistory()->create([
        //            'from_stage_id' => $meta['from_stage_id'] ?? null,
        //            'to_stage_id' => $meta['to_stage_id'] ?? $this->application->current_stage_id,
        //            'moved_by' => $meta['by_user_id'] ?? null,
        //            'notes' => $meta['notes'] ?? null,
        //        ]);
        if (isset($meta['to_stage_id'])) {
            $fromStage = $this->application->current_stage_id;

            $toStage = Stage::query()
                ->where('job_requisition_id', $this->application->requisition_id)
                ->where('active', true)
                ->where('display_order', '>', $this->application->currentStage()?->display_order)
                ->orderBy('display_order')
                ->value('id');

            $this->application->update([
                'current_stage_id' => $this->application->current_stage_id,
                'status' => ApplicationStatusEnum::InProgress,
            ]);

            $this->application->stageHistory()->create([
                'from_stage_id' => $fromStage,
                'to_stage_id' => $toStage,
                'moved_by' => $meta['by_user_id'] ?? null,
                'notes' => $meta['notes'] ?? null,
            ]);

            return;
        }

        match (ApplicationStatusEnum::tryFrom($meta['to_status'])) {
            ApplicationStatusEnum::InProgress => null,
            ApplicationStatusEnum::OfferExtended => null,
            default => null,
        };
        // Offer extended flow
        if (isset($meta['to_status']) && $meta['to_status'] === ApplicationStatusEnum::OfferExtended->value) {
            $this->application->update([
                'status' => ApplicationStatusEnum::OfferExtended,
                'offer_extended_at' => $meta['offer_extended_at'] ?? now(),
                'offer_extended_by' => $meta['by_user_id'] ?? null,
                'offer_amount' => $meta['offer_amount'] ?? $this->application->offer_amount,
                'offer_response_deadline' => $meta['offer_response_deadline'] ?? $this->application->offer_response_deadline,
            ]);

            return;
        }

        $this->application->update(['status' => ApplicationStatusEnum::InProgress]);
    }

    public function notify(array $meta = []): void
    {
        // no-op
    }
}
