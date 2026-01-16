<?php

declare(strict_types=1);

use He4rt\Permissions\PermissionsEnum;
use He4rt\Permissions\Roles;
use He4rt\Users\User;

return [
    'permissions' => [
        Roles::SuperAdmin->value => [
            User::class => PermissionsEnum::cases(),
        ],
    ],
];
