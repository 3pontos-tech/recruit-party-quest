<?php

declare(strict_types=1);

use He4rt\Candidates\Models\Candidate;
use He4rt\Candidates\Models\Education;
use He4rt\Candidates\Models\Skill;
use He4rt\Candidates\Models\WorkExperience;
use He4rt\Permissions\PermissionsEnum;
use He4rt\Permissions\Roles;

return [
    'permissions' => [
        Roles::SuperAdmin->value => [
            Candidate::class => PermissionsEnum::cases(),
            Education::class => PermissionsEnum::cases(),
            Skill::class => PermissionsEnum::cases(),
            WorkExperience::class => PermissionsEnum::cases(),
        ],
    ],
];
