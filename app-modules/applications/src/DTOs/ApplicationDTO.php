<?php

declare(strict_types=1);

namespace He4rt\Applications\DTOs;

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\CandidateSourceEnum;
use Ramsey\Uuid\Uuid;

final readonly class ApplicationDTO
{
    public function __construct(
        public string|Uuid $applicationId,
        public string $requisitionId,
        public string $candidateId,
        public string $teamId,
        public ApplicationStatusEnum $status,
        public CandidateSourceEnum $source,
    ) {}

    /**
     * @param array{
     *   application_id: string,
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
            applicationId: $data['application_id'],
            requisitionId: $data['requisition_id'],
            candidateId: $data['candidate_id'],
            teamId: $data['team_id'],
            status: ApplicationStatusEnum::from($data['status']),
            source: CandidateSourceEnum::from($data['source']),
        );
    }
}
