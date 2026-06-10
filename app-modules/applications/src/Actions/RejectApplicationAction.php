<?php

declare(strict_types=1);

namespace He4rt\Applications\Actions;

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Services\Transitions\TransitionData;

final class RejectApplicationAction
{
    public function execute(string $applicationId, RejectionReasonCategoryEnum $reason, ?string $details = null): void
    {
        $application = Application::query()->findOrFail($applicationId);

        $application->current_step->handle(TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Rejected,
            'rejection_reason_category' => $reason->value,
            'rejection_reason_details' => $details,
        ]));
    }
}
