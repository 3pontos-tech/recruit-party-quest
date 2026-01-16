<?php

declare(strict_types=1);

namespace He4rt\Permissions\Tests\Feature\Commands;

use He4rt\Permissions\Permission;
use He4rt\Permissions\Role;
use He4rt\Permissions\Roles;
use He4rt\Users\User;
use Illuminate\Support\Facades\Artisan;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

it('synchronizes roles and permissions', function (): void {
    // Ensure the default super admin user exists
    User::factory()->create([
        'email' => 'admin@admin.com',
    ]);

    Artisan::call('sync:permissions');

    $permissionCount = Permission::query()->count();

    foreach (Roles::cases() as $role) {
        assertDatabaseHas('rbac_roles', [
            'name' => $role->value,
            'guard_name' => 'web',
        ]);
    }

    assertDatabaseCount('rbac_permissions', $permissionCount);

    $superAdmin = Role::findByName(Roles::SuperAdmin->value);
    expect($superAdmin->permissions)->toHaveCount($permissionCount);

    $user = User::query()->where('email', 'admin@admin.com')->first();
    expect($user->hasRole(Roles::SuperAdmin->value))->toBeTrue();
});
