<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Requisitions\Jobs;

use Filament\Notifications\Notification;
use He4rt\Recruitment\Requisitions\Actions\AiJobRequisition\GenerateJobRequisition;
use He4rt\Recruitment\Requisitions\Actions\AiJobRequisition\GenerateJobRequisitionDTO;
use He4rt\Recruitment\Requisitions\Actions\StoreJobRequisitionAction;
use He4rt\Recruitment\Requisitions\DTOs\JobRequisitionDTO;
use He4rt\Users\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GeneratePostJob implements ShouldQueue
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
        $notifiable = User::whereId($this->dto->createdBy)->first();

        Notification::make()
            ->success()
            ->title('Job Saved Successfully')
            ->broadcast($notifiable);
        // may handle right there tha something went wrong
        // receives $result and then dispatch an event, on dispatching the event creates the JobRequisition, and then dispatch another event to notify the creator that was successfully or not
    }
}
