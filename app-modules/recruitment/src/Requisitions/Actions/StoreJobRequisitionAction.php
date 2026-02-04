<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Requisitions\Actions;

use He4rt\Recruitment\Requisitions\DTOs\JobPostingDTO;
use He4rt\Recruitment\Requisitions\DTOs\JobRequisitionDTO;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;

final class StoreJobRequisitionAction
{
    public function execute(JobRequisitionDTO $dto): void
    {
        $jobRequisition = JobRequisition::query()->create([
            'slug' => $dto->slug,
            'team_id' => $dto->teamId,
            'department_id' => $dto->departmentId,
            'work_arrangement' => $dto->workArrangement,
            'employment_type' => $dto->employmentType,
            'experience_level' => $dto->experienceLevel,
            'salary_currency' => 'BRL',
            'positions_available' => 1,
            'show_salary_to_candidates' => false,
            'recruiter_id' => $dto->recruiterId,
            'created_by_id' => $dto->createdBy,
            'status' => $dto->status,
            'priority' => $dto->priority,
            'is_confidential' => false,
            'is_internal_only' => false,
        ]);

        foreach ($dto->items as $index => $item) {
            $jobRequisition->items()->create([
                'type' => $item->type->value,
                'content' => $item->content,
                'order' => $index + 1,
            ]);
        }

        $jobPostingDTO = JobPostingDTO::make([
            'job_requisition_id' => $jobRequisition->getKey(),
            'title' => $dto->title,
            'slug' => $dto->slug,
            'team_id' => $dto->teamId,
            'summary' => $dto->summary,
            'description' => $dto->description,
        ]);

        resolve(CreateJobPostingAction::class)->execute($jobPostingDTO);
    }
}
