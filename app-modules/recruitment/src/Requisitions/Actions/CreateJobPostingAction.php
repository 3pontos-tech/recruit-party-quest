<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Requisitions\Actions;

use He4rt\Recruitment\Requisitions\DTOs\JobPostingDTO;
use He4rt\Recruitment\Requisitions\Models\JobPosting;

final class CreateJobPostingAction
{
    public function execute(JobPostingDTO $dto): void
    {
        JobPosting::query()->create([
            'job_requisition_id' => $dto->jobRequisitionId,
            'title' => $dto->title,
            'team_id' => $dto->teamId,
            'summary' => $dto->summary,
            'description' => $dto->description,
            'slug' => $dto->slug,
            'external_post_url' => 'https://google.com',
        ]);
    }
}
