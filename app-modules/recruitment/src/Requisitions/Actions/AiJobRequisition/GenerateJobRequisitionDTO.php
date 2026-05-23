<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Requisitions\Actions\AiJobRequisition;

use He4rt\Recruitment\Requisitions\Enums\EmploymentTypeEnum;
use He4rt\Recruitment\Requisitions\Enums\ExperienceLevelEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionPriorityEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkArrangementEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkScheduleEnum;
use JsonSerializable;

final readonly class GenerateJobRequisitionDTO implements JsonSerializable
{
    public function __construct(
        public string $title,
        public string $description,
        public ExperienceLevelEnum $experienceLevel,
        public ?EmploymentTypeEnum $employmentType,
        public ?WorkScheduleEnum $workSchedule,
        public WorkArrangementEnum $workArrangement,
        public ?RequisitionPriorityEnum $priority,
        public RequisitionStatusEnum $status,
        public int $positions,
        public string $recruiterId,
        public ?string $companyDescription,
        public string $departmentId,
        public string $teamId,
        public string $createdBy,

    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function make(array $data): self
    {
        return new self(
            title: $data['title'],
            description: $data['description'],
            experienceLevel: ExperienceLevelEnum::from($data['experience_level']),
            employmentType: isset($data['employment_type']) ? EmploymentTypeEnum::tryFrom($data['employment_type']) : null,
            workSchedule: isset($data['work_schedule']) ? WorkScheduleEnum::tryFrom($data['work_schedule']) : null,
            workArrangement: WorkArrangementEnum::from($data['work_arrangement']),
            priority: RequisitionPriorityEnum::from($data['priority']),
            status: RequisitionStatusEnum::PendingApproval,
            positions: 1,
            recruiterId: $data['recruiter_id'],
            companyDescription: $data['company_description'] ?? null,
            departmentId: $data['department_id'],
            teamId: $data['team_id'],
            createdBy: $data['created_by']
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'experience_level' => $this->experienceLevel,
            'employment_type' => $this->employmentType,
            'work_schedule' => $this->workSchedule,
            'work_arrangement' => $this->workArrangement,
            'priority' => $this->priority,
            'status' => $this->status,
            'positions' => $this->positions,
            'recruiter_id' => $this->recruiterId,
        ];
    }
}
