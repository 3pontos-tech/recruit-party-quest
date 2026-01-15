# Permissions Module

This module provides a dynamic Role-Based Access Control (RBAC) system using Spatie Laravel Permission.

## Roles

Roles are managed through the `He4rt\Permissions\Roles` enum. To add a new role, add a new case to this enum.

## Permissions

Permissions are automatically generated for all models registered in the `Relation::morphMap()`. For each model, the
following actions from `PermissionsEnum` are created:

- view
- view_any
- create
- update
- delete
- restore
- force_delete

Permission names follow the pattern: `{action}_{model_morph_alias}`.

## Module Configuration

Each module can define its own role-permission assignments by creating a `config/rbac.php` file.

### Example rbac.php

```php
<?php

declare(strict_types=1);

use He4rt\Permissions\PermissionsEnum;
use He4rt\Permissions\Roles;
use App\Models\Post;

return [
    'permissions' => [
        Roles::User->value => [
            Post::class => [
                PermissionsEnum::View,
                PermissionsEnum::ViewAny,
                PermissionsEnum::Create,
            ],
        ],
    ],
];
```

## Synchronization

To synchronize roles, permissions, and module assignments with the database, run:

```bash
php artisan sync:permissions
```

This command will:

1. Clear the permission cache.
2. Ensure all roles from the `Roles` enum exist.
3. Generate permissions for all models in the `Relation::morphMap()`.
4. Sync permissions to roles as defined in all `config/rbac.php` files.
5. Grant all permissions to the `super_admin` role.
