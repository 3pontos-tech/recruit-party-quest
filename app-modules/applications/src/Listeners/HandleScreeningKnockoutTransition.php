<?php

declare(strict_types=1);

namespace He4rt\Applications\Listeners;

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
use He4rt\Applications\Services\Transitions\TransitionData;
use He4rt\Screening\Events\ScreeningEvaluated;

final class HandleScreeningKnockoutTransition
{
    public function handle(ScreeningEvaluated $event): void
    {
        $application = $event->application;
        $application->loadMissing('requisition');

        if ($application->requisition?->auto_screening_transition !== true) {
            return;
        }

        if ($application->status !== ApplicationStatusEnum::New) {
            return;
        }

        if ($event->anyKnockoutFailed) {
            $data = TransitionData::fromArray([
                'to_status' => ApplicationStatusEnum::Rejected,
                'rejection_reason_category' => RejectionReasonCategoryEnum::ScreeningKnockout->value,
                // TODO: verificar qual dos dois campos ficam visiveis para o usuario final e editar para algo mais agradavel.
                'rejection_reason_details' => __('screening::messages.knockout_auto_rejected'),
                'notes' => __('screening::messages.knockout_auto_rejected'),
            ]);

            $application->current_step->handle($data);

            return;
        }

        if ($event->hadKnockoutCriteria) {
            $data = TransitionData::fromArray([
                'to_status' => ApplicationStatusEnum::InReview,
                'notes' => __('screening::messages.knockout_auto_advanced'),
            ]);

            $application->current_step->handle($data);
        }
    }
}
