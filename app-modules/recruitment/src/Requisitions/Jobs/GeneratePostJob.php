<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Requisitions\Jobs;

use Filament\Notifications\Notification;
use He4rt\Recruitment\Requisitions\Actions\AiJobRequisition\GenerateJobRequisition;
use He4rt\Recruitment\Requisitions\Actions\AiJobRequisition\GenerateJobRequisitionDTO;
use He4rt\Recruitment\Requisitions\Actions\StoreJobRequisitionAction;
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

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 1;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 120;

    public function __construct(
        public GenerateJobRequisitionDTO $dto
    ) {}

    /**
     * @throws Throwable
     * @throws GenerateJobRequisitionException
     */
    public function handle(): void
    {
        try {
            /** @var JobRequisitionDTO $result */
            $result = resolve(GenerateJobRequisition::class)->execute($this->dto);
            $jobRequisition = resolve(StoreJobRequisitionAction::class)->execute($result);

            broadcast(new JobRequisitionGenerationEvent(
                JobGenerationStatus::Success,
                $this->dto->createdBy,
                $jobRequisition->getKey()
            ));

            $notifiable = User::whereId($this->dto->createdBy)->first();

            Notification::make()
                ->success()
                ->title(__('recruitment::filament.requisition.job_posting.notifications.successful'))
                ->broadcast($notifiable);
        } catch (Throwable $throwable) {
            broadcast(new JobRequisitionGenerationEvent(
                JobGenerationStatus::Error,
                $this->dto->createdBy,
                errorMessage: __('recruitment::filament.requisition.job_posting.notifications.failed')
            ));

            throw $throwable;
        }
    }

    public function failed(?Throwable $exception): void
    {
        broadcast(new JobRequisitionGenerationEvent(
            JobGenerationStatus::Error,
            $this->dto->createdBy,
            errorMessage: __('recruitment::filament.requisition.job_posting.notifications.failed')
        ));
    }
}
