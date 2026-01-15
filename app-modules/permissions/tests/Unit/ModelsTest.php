<?php

declare(strict_types=1);

namespace He4rt\Permissions\Tests\Unit;

use He4rt\Permissions\Permission;
use He4rt\Permissions\Role;
use He4rt\Users\User;

it('role model uses correct table', function (): void {
    $role = new Role();
    expect($role->getTable())->toBe('rbac_roles');
});

it('permission model uses correct table', function (): void {
    $permission = new Permission();
    expect($permission->getTable())->toBe('rbac_permissions');
});

it('permission model has formatted_name attribute', function (): void {
    $permission = new Permission([
        'name' => 'view',
        'resource' => User::class,
        'action' => 'viewAny',
        'resource_group' => 'Admin',
    ]);

    expect($permission->formatted_name)->toBe('Admin-User-viewAny-view');
});

it('permission model has resource_model attribute', function (): void {
    $permission = new Permission([
        'resource' => User::class,
    ]);

    expect($permission->resource_model)->toBe('User');
});
