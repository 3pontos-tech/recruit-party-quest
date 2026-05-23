<?php

declare(strict_types=1);

use He4rt\Feedback\Models\Evaluation;
use He4rt\Permissions\PermissionsEnum;
use He4rt\Permissions\Roles;

return [
    'permissions' => [
        Roles::SuperAdmin->value => [
            Evaluation::class => PermissionsEnum::cases(),
        ],
    ],
];
