<?php

declare(strict_types=1);

return [
    'resource' => [
        'label' => 'Role',
        'plural_label' => 'Roles',
        'navigation_label' => 'Roles',
    ],
    'fields' => [
        'name' => 'Name',
        'guard_name' => 'Guard Name',
        'permissions' => 'Permissions',
        'created_at' => 'Created Date',
        'updated_at' => 'Updated At',
    ],
    'tabs' => [
        'permissions' => 'Permissions',
    ],
];
