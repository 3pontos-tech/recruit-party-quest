<?php

declare(strict_types=1);

namespace He4rt\Applications\Listeners;

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Jobs\RejectScreeningKnockoutJob;
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
            dispatch(new RejectScreeningKnockoutJob($application))->delay(now()->addDay());

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
