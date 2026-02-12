<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Requisitions\Jobs;

use Filament\Notifications\Notification;
use He4rt\Recruitment\Requisitions\Actions\AiJobRequisition\GenerateJobRequisitionDTO;
use He4rt\Recruitment\Requisitions\DTOs\JobRequisitionDTO;
use He4rt\Recruitment\Requisitions\Enums\JobGenerationStatus;
use He4rt\Recruitment\Requisitions\Events\JobRequisitionGenerationEvent;
use He4rt\Recruitment\Requisitions\Exceptions\GenerateJobRequisitionException;
use He4rt\Users\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class GeneratePostJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public GenerateJobRequisitionDTO $dto
    ) {}

    /**
     * @throws Throwable
     * @throws GenerateJobRequisitionException
     */
    public function handle(): void
    {
        // Broadcast Processing status
        broadcast(new JobRequisitionGenerationEvent(
            JobGenerationStatus::Processing,
            $this->dto->createdBy
        ));

        try {
            /** @var JobRequisitionDTO $result */
            //            $result = resolve(GenerateJobRequisition::class)->execute($this->dto);
            //            $jobRequisition = resolve(StoreJobRequisitionAction::class)->execute($result);

            // Broadcast Success with job requisition data
            broadcast(new JobRequisitionGenerationEvent(
                JobGenerationStatus::Success,
                $this->dto->createdBy,
                //                $jobRequisition->getKey()
            ));

            $notifiable = User::whereId($this->dto->createdBy)->first();

            Notification::make()
                ->success()
                ->title(__('recruitment::filament.requisition.job_posting.notifications.successful'))
                ->broadcast($notifiable);
        } catch (Throwable $throwable) {
            // Broadcast Error with error message
            broadcast(new JobRequisitionGenerationEvent(
                JobGenerationStatus::Error,
                $this->dto->createdBy,
                errorMessage: $throwable->getMessage()
            ));

            // Re-throw to mark job as failed
            throw $throwable;
        }
    }
}
