<?php

declare(strict_types=1);

namespace He4rt\Feedback\DTOs;

use He4rt\Feedback\Enums\EvaluationRatingEnum;

final readonly class EvaluationDTO
{
    public function __construct(
        public string $teamId,
        public string $applicationId,
        public string $stageId,
        public string $evaluatorId,
        public EvaluationRatingEnum $overallRating,
        public ?string $recommendation,
        public ?string $strengths,
        public ?string $concerns,
        public ?string $notes,
        public CriteriaScoresDTO $criteriaScores,

    ) {}

    /**
     * @param array{
     *   team_id: string,
     *   application_id: string,
     *   stage_id: string,
     *   evaluator_id: string,
     *   overall_rating: string|int,
     *   recommendation?: string|null,
     *   strengths?: string|null,
     *   concerns?: string|null,
     *   notes?: string|null,
     *   criteria_scores: CriteriaScoresDTO
     * } $data
     */
    public static function make(array $data): self
    {
        return new self(
            teamId: $data['team_id'],
            applicationId: $data['application_id'],
            stageId: $data['stage_id'],
            evaluatorId: $data['evaluator_id'],
            overallRating: EvaluationRatingEnum::from($data['overall_rating']),
            recommendation: $data['recommendation'],
            strengths: $data['strengths'],
            concerns: $data['concerns'],
            notes: $data['notes'],
            criteriaScores: $data['criteria_scores'],
        );
    }
}
