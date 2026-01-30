<?php

declare(strict_types=1);

namespace He4rt\Feedback\Actions;

use He4rt\Feedback\DTOs\EvaluationDTO;
use He4rt\Feedback\Models\Evaluation;

final class StoreEvaluationAction
{
    public function execute(EvaluationDTO $dto): void
    {
        Evaluation::query()->create([
            'team_id' => $dto->teamId,
            'application_id' => $dto->applicationId,
            'stage_id' => $dto->stageId,
            'evaluator_id' => $dto->evaluatorId,
            'overall_rating' => $dto->overallRating,
            'recommendation' => $dto->recommendation,
            'strengths' => $dto->strengths,
            'concerns' => $dto->concerns,
            'notes' => $dto->notes,
            'criteria_scores' => $dto->criteriaScores->jsonSerialize(),
        ]);
    }
}
