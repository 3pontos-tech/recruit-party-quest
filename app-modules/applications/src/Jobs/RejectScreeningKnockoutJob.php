<?php

declare(strict_types=1);

namespace He4rt\Applications\Jobs;

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Services\Transitions\TransitionData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class RejectScreeningKnockoutJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public Application $application) {}

    public function handle(): void
    {
        $application = $this->application->fresh(['requisition']);

        if ($application === null) {
            return;
        }

        if ($application->requisition?->auto_screening_transition !== true) {
            return;
        }

        if ($application->status !== ApplicationStatusEnum::New) {
            return;
        }

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Rejected,
            'rejection_reason_category' => RejectionReasonCategoryEnum::ScreeningKnockout,
            'rejection_reason_details' => __('screening::messages.knockout_auto_rejected'),
            'notes' => __('screening::messages.knockout_auto_rejected'),
        ]);

        $application->current_step->handle($data);
    }
}
