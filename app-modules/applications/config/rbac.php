<?php

declare(strict_types=1);

use He4rt\Applications\Models\Application;
use He4rt\Applications\Models\ApplicationStageHistory;
use He4rt\Permissions\PermissionsEnum;
use He4rt\Permissions\Roles;

return [
    'permissions' => [
        Roles::SuperAdmin->value => [
            Application::class => PermissionsEnum::cases(),
            ApplicationStageHistory::class => PermissionsEnum::cases(),
        ],
    ],
];
