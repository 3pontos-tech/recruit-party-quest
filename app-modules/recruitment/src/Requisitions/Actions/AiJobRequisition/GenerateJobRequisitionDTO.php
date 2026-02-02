<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Requisitions\Actions\AiJobRequisition;

use He4rt\Recruitment\Requisitions\Enums\EmploymentTypeEnum;
use He4rt\Recruitment\Requisitions\Enums\ExperienceLevelEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionPriorityEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkArrangementEnum;

class GenerateJobRequisitionDTO
{
    public function __construct(
        public string $title,
        public string $description,
        public ExperienceLevelEnum $experienceLevel,
        public EmploymentTypeEnum $employmentType,
        public WorkArrangementEnum $workArrangement,
        public ?RequisitionPriorityEnum $priority = null
    ) {}
}
