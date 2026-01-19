<?php

declare(strict_types=1);

namespace He4rt\Applications\Services\Transitions;

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Exceptions\InvalidTransitionException;
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
        // TODO: acredito que aqui deveria ser um match similar aos outros transitions
        // TODO: não se se o stage deve ser criado aqui ou atualizado

        if (isset($meta['to_stage_id'])) {
            $stage = Stage::query()->find($meta['to_stage_id']);

            throw_if(! $stage || $stage->job_requisition_id !== $this->application->requisition_id,
                InvalidTransitionException::class, 'Invalid stage for this requisition');

            $this->application->current_stage_id = $stage->id;
            $this->application->status = ApplicationStatusEnum::InProgress;
            $this->application->save();

            $this->application->stageHistory()->create([
                'from_stage_id' => $this->application->current_stage_id,
                'to_stage_id' => $stage->id,
                'moved_by' => $meta['by_user_id'] ?? null,
                'notes' => $meta['notes'] ?? null,
            ]);

            return;
        }

        // Offer extended flow
        if (isset($meta['to_status']) && $meta['to_status'] === ApplicationStatusEnum::OfferExtended->value) {
            $this->application->update([
                'status' => ApplicationStatusEnum::OfferExtended,
                'offer_extended_at' => $meta['offer_extended_at'] ?? now(),
                'offer_extended_by' => $meta['by_user_id'] ?? null,
                'offer_amount' => $meta['offer_amount'] ?? $this->application->offer_amount,
                'offer_response_deadline' => $meta['offer_response_deadline'] ?? $this->application->offer_response_deadline,
            ]);

            // persist stage history if provided
            if (isset($meta['to_stage_id']) || isset($meta['from_stage_id'])) {
                $this->application->stageHistory()->create([
                    'from_stage_id' => $meta['from_stage_id'] ?? null,
                    'to_stage_id' => $meta['to_stage_id'] ?? $this->application->current_stage_id,
                    'moved_by' => $meta['by_user_id'] ?? null,
                    'notes' => $meta['notes'] ?? null,
                ]);
            }

            return;
        }

        // Generic set to in_progress
        $this->application->status = ApplicationStatusEnum::InProgress;
        $this->application->save();
    }

    public function notify(array $meta = []): void
    {
        // no-op
    }
}
