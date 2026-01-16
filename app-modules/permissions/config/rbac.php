<?php

declare(strict_types=1);

use He4rt\Permissions\Permission;
use He4rt\Permissions\PermissionsEnum;
use He4rt\Permissions\Role;
use He4rt\Permissions\Roles;

return [
    'permissions' => [
        Roles::SuperAdmin->value => [
            Role::class => PermissionsEnum::cases(),
            Permission::class => [
                PermissionsEnum::ViewAny,
                PermissionsEnum::View,
                PermissionsEnum::Create,
                PermissionsEnum::Update,
                PermissionsEnum::Delete,
                PermissionsEnum::ForceDelete,
                PermissionsEnum::Restore,
            ],
        ],
    ],
];
