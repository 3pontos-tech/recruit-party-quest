<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Requisitions\DTOs;

use He4rt\Recruitment\Requisitions\Enums\EmploymentTypeEnum;
use He4rt\Recruitment\Requisitions\Enums\ExperienceLevelEnum;
use He4rt\Recruitment\Requisitions\Enums\JobRequisitionItemTypeEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionPriorityEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkArrangementEnum;
use Illuminate\Support\Str;

final readonly class JobRequisitionDTO
{
    public function __construct(
        public string $title,
        public string $slug,
        public string $departmentId,
        public string $teamId,
        public string $recruiterId,
        public string $description,
        public ExperienceLevelEnum $experienceLevel,
        public EmploymentTypeEnum $employmentType,
        public WorkArrangementEnum $workArrangement,
        public ?RequisitionPriorityEnum $priority,
        public RequisitionStatusEnum $status,
        public string $summary,
        public string $createdBy,

        /** @var JobRequisitionItemDTO[] */
        public array $items,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function make(array $data): self
    {
        return new self(
            title: $data['title'],
            slug: Str::slug($data['slug']),
            departmentId: $data['department_id'],
            teamId: $data['team_id'],
            recruiterId: $data['recruiter_id'],
            description: $data['description'],
            experienceLevel: $data['experience_level'],
            employmentType: $data['employment_type'],
            workArrangement: $data['work_arrangement'],
            priority: $data['priority'] ?? null,
            status: $data['status'],
            summary: $data['summary'],
            createdBy: $data['created_by'],
            items: self::mapItems($data['items']),
        );
    }

    /**
     * @param array{
     *   responsibilities?: string[],
     *   required_qualifications?: string[],
     *   preferred_qualifications?: string[],
     *   benefits?: string[],
     * } $items
     * @return JobRequisitionItemDTO[]
     */
    private static function mapItems(array $items): array
    {
        $typeMap = [
            'responsibilities' => JobRequisitionItemTypeEnum::Responsibility,
            'required_qualifications' => JobRequisitionItemTypeEnum::RequiredQualification,
            'preferred_qualifications' => JobRequisitionItemTypeEnum::PreferredQualification,
            'benefits' => JobRequisitionItemTypeEnum::Benefit,
        ];

        $dtos = [];

        foreach ($typeMap as $key => $typeEnum) {
            foreach ($items[$key] ?? [] as $index => $content) {
                $dtos[] = JobRequisitionItemDTO::make([
                    'type' => $typeEnum->value,
                    'content' => $content,
                    'order' => (int) $index + 1,
                ]);
            }
        }

        return $dtos;
    }
}
