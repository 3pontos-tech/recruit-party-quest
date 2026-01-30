<?php

declare(strict_types=1);

namespace He4rt\Applications\Actions;

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
use He4rt\Applications\Models\Application;

final class RejectApplicationAction
{
    public function execute(string $applicationId, RejectionReasonCategoryEnum $reason, ?string $details = null): void
    {
        $application = Application::query()->where('id', $applicationId)->first();
        $application->update([
            'status' => ApplicationStatusEnum::Rejected,
            'rejection_reason_category' => $reason,
            'rejection_reason_details' => $details,
            'rejected_at' => now(),
        ]);
    }
}
