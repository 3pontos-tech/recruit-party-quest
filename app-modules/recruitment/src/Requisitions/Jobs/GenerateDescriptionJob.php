<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Requisitions\Jobs;

use He4rt\Recruitment\Requisitions\Actions\AiJobRequisition\GenerateJobRequisition;
use He4rt\Recruitment\Requisitions\Actions\AiJobRequisition\GenerateJobRequisitionDTO;
use He4rt\Recruitment\Requisitions\Actions\StoreJobRequisitionAction;
use He4rt\Recruitment\Requisitions\DTOs\JobRequisitionDTO;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateDescriptionJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public GenerateJobRequisitionDTO $dto
    ) {}

    public function handle(): void
    {
        /** @var JobRequisitionDTO $result */
        $result = resolve(GenerateJobRequisition::class)->execute($this->dto);
        resolve(StoreJobRequisitionAction::class)->execute($result);
        // may handle right there tha something went wrong
        // recieves $result and then dispatch an event, on dispatching the event creates the JobRequisition, and then dispatch another event to notify the creator that was successfully or not
    }
}
