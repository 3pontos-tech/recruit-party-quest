<?php

declare(strict_types=1);

use He4rt\Permissions\PermissionsEnum;
use He4rt\Permissions\Roles;
use He4rt\Screening\Models\ScreeningQuestion;
use He4rt\Screening\Models\ScreeningResponse;

return [
    'permissions' => [
        Roles::SuperAdmin->value => [
            ScreeningQuestion::class => PermissionsEnum::cases(),
            ScreeningResponse::class => PermissionsEnum::cases(),
        ],
    ],
];
