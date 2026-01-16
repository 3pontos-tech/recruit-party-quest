<?php

declare(strict_types=1);

use He4rt\Location\Address;
use He4rt\Permissions\PermissionsEnum;
use He4rt\Permissions\Roles;

return [
    'permissions' => [
        Roles::SuperAdmin->value => [
            Address::class => PermissionsEnum::cases(),
        ],
    ],
];
