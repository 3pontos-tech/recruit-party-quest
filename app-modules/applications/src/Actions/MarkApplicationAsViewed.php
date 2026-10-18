<?php

declare(strict_types=1);

namespace He4rt\Applications\Actions;

use He4rt\Applications\Models\Application;
use He4rt\Applications\Models\ApplicationView;
use He4rt\Users\User;

/** Registra a primeira visualização da candidatura pelo time; chamadas seguintes devolvem o registro existente. */
final class MarkApplicationAsViewed
{
    public function execute(Application $application, User $viewer): ApplicationView
    {
        return ApplicationView::query()->firstOrCreate(
            ['application_id' => $application->getKey()],
            [
                'team_id' => $application->team_id,
                'viewed_by' => $viewer->getKey(),
                'viewed_at' => now(),
            ],
        );
    }
}
