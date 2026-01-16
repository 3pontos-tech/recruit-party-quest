<?php

declare(strict_types=1);

use He4rt\Permissions\PermissionsEnum;
use He4rt\Permissions\Roles;
use He4rt\Teams\Department;
use He4rt\Teams\Team;

return [
    'permissions' => [
        Roles::SuperAdmin->value => [
            Department::class => PermissionsEnum::cases(),
            Team::class => PermissionsEnum::cases(),
        ],
    ],
];
