<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Requisitions\DTOs;

final readonly class JobPostingDTO
{
    public function __construct(
        public string $jobRequisitionId,
        public string $title,
        public string $slug,
        public string $description,
        public string $summary,
        public string $teamId,
    ) {}

    /**
     * @param  array<string, string>  $data
     */
    public static function make(array $data): self
    {
        return new self(
            jobRequisitionId: $data['job_requisition_id'],
            title: $data['title'],
            slug: $data['slug'],
            description: $data['description'],
            summary: $data['summary'],
            teamId: $data['team_id'],
        );
    }
}
