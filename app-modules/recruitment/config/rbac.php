<?php

declare(strict_types=1);

use He4rt\Permissions\PermissionsEnum;
use He4rt\Permissions\Roles;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Stages\Models\Stage;

return [
    'permissions' => [
        Roles::SuperAdmin->value => [
            JobPosting::class => PermissionsEnum::cases(),
            JobRequisition::class => PermissionsEnum::cases(),
            Stage::class => PermissionsEnum::cases(),
        ],
    ],
];
