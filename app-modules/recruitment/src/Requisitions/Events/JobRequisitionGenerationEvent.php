<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Requisitions\Events;

use He4rt\Recruitment\Requisitions\Enums\JobGenerationStatus;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class JobRequisitionGenerationEvent implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly JobGenerationStatus $status,
        public readonly string $userId,
        //        public readonly ?int $jobRequisitionId = null,
        public readonly ?string $errorMessage = null,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('job-requisition.generation.'.$this->userId),
        ];
    }

    public function broadcastAs(): string
    {
        return $this->status->value;
    }

    public function broadcastWith(): array
    {
        return [
            'status' => $this->status->value,
            //            'job_requisition_id' => $this->jobRequisitionId,
            'error_message' => $this->errorMessage,
        ];
    }
}
