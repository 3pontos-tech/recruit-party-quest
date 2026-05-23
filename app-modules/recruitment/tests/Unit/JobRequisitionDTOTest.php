<?php

declare(strict_types=1);

use He4rt\Recruitment\Requisitions\DTOs\JobRequisitionDTO;
use He4rt\Recruitment\Requisitions\Enums\ExperienceLevelEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkArrangementEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkScheduleEnum;

it('builds with nullable employment type and optional work schedule', function (): void {
    $dto = JobRequisitionDTO::make([
        'title' => 'Dev', 'department_id' => 'd', 'team_id' => 't', 'recruiter_id' => 'r',
        'description' => 'x', 'experience_level' => ExperienceLevelEnum::Junior,
        'employment_type' => null, 'work_arrangement' => WorkArrangementEnum::Remote,
        'status' => RequisitionStatusEnum::Draft, 'summary' => 's', 'created_by' => 'u', 'items' => [],
    ]);

    expect($dto->employmentType)->toBeNull()->and($dto->workSchedule)->toBeNull();

    $dto2 = JobRequisitionDTO::make([
        'title' => 'Dev', 'department_id' => 'd', 'team_id' => 't', 'recruiter_id' => 'r',
        'description' => 'x', 'experience_level' => ExperienceLevelEnum::Junior,
        'employment_type' => null, 'work_arrangement' => WorkArrangementEnum::Remote,
        'work_schedule' => WorkScheduleEnum::Shift,
        'status' => RequisitionStatusEnum::Draft, 'summary' => 's', 'created_by' => 'u', 'items' => [],
    ]);

    expect($dto2->workSchedule)->toBe(WorkScheduleEnum::Shift);
});
