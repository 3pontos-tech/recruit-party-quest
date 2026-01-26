<?php

declare(strict_types=1);

namespace He4rt\Applications\Services\Applications;

use He4rt\Applications\DTOs\ApplicationDTO;
use He4rt\Applications\Models\Application;

final class StoreApplication
{
    public function execute(ApplicationDTO $dto): void
    {
        $application = Application::query()->create([
            'id' => $dto->applicationId,
            'requisition_id' => $dto->requisitionId,
            'candidate_id' => $dto->candidateId,
            'team_id' => $dto->teamId,
            'status' => $dto->status,
            'source' => $dto->source,
        ]);

        $application->update([
            'current_stage_id' => $application->first_stage->getKey(),
        ]);
    }
}
