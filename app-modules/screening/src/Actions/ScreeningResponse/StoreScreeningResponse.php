<?php

declare(strict_types=1);

namespace He4rt\Screening\Actions\ScreeningResponse;

use He4rt\Screening\Collections\ScreeningResponseCollection;
use He4rt\Screening\Models\ScreeningResponse;

final class StoreScreeningResponse
{
    public function execute(ScreeningResponseCollection $responses): void
    {
        foreach ($responses as $response) {
            ScreeningResponse::query()->create([
                'team_id' => $response->teamId,
                'application_id' => $response->applicationId,
                'question_id' => $response->questionId,
                'response_value' => $response->response_value,
            ]);
        }
    }
}
