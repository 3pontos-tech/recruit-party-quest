<?php

declare(strict_types=1);

namespace He4rt\Applications\DTOs;

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\CandidateSourceEnum;

final readonly class ApplicationDTO
{
    public function __construct(
        public string $requisitionId,
        public string $candidateId,
        public string $teamId,
        public ApplicationStatusEnum $status,
        public CandidateSourceEnum $source,
    ) {}

    /**
     * @param array{
     *   requisition_id: string,
     *   candidate_id: string,
     *   team_id: string,
     *   status: string,
     *   source: string,
     * } $data
     */
    public static function make(array $data): self
    {
        return new self(
            requisitionId: $data['requisition_id'],
            candidateId: $data['candidate_id'],
            teamId: $data['team_id'],
            status: ApplicationStatusEnum::from($data['status']),
            source: CandidateSourceEnum::from($data['source']),
        );
    }
}
