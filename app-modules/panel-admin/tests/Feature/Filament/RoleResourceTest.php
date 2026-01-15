<?php

declare(strict_types=1);

namespace He4rt\Permissions\Tests\Feature\Filament;

use Filament\Facades\Filament;
use He4rt\Admin\Filament\Resources\Permissions\Pages\CreateRole;
use He4rt\Admin\Filament\Resources\Permissions\Pages\EditRole;
use He4rt\Admin\Filament\Resources\Permissions\Pages\ListRoles;
use He4rt\Admin\Filament\Resources\Permissions\RoleResource;
use He4rt\Permissions\Permission;
use He4rt\Permissions\Role;
use He4rt\Permissions\Roles;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    actingAs(User::factory()->create());

    // Sync permissions so we have some to work with
    artisan('sync:permissions');

    // Give the user SuperAdmin role to bypass all policies
    auth()->user()->assignRole(Roles::SuperAdmin->value);
});

it('can list roles', function (): void {
    $roles = Role::factory()->count(5)->create();

    livewire(ListRoles::class)
        ->assertCanSeeTableRecords($roles)
        ->assertCountTableRecords(5 + count(Roles::cases())); // 5 + default roles
});

it('can render create role page', function (): void {
    get(RoleResource::getUrl('create'))
        ->assertSuccessful();
});

it('can create role', function (): void {
    $newData = [
        'name' => 'Test Role',
        'guard_name' => 'web',
    ];

    livewire(CreateRole::class)
        ->fillForm($newData)
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas('rbac_roles', [
        'name' => 'Test Role',
        'guard_name' => 'web',
    ]);
});

it('can render edit role page', function (): void {
    $role = Role::factory()->create();

    get(RoleResource::getUrl('edit', ['record' => $role]))
        ->assertSuccessful();
});

it('can update role', function (): void {
    $role = Role::factory()->create();
    $newData = [
        'name' => 'Updated Role Name',
    ];

    livewire(EditRole::class, [
        'record' => $role->getRouteKey(),
    ])
        ->fillForm($newData)
        ->call('save')
        ->assertHasNoFormErrors();

    expect($role->refresh())
        ->name->toBe('Updated Role Name');
});

it('can sync permissions to role', function (): void {
    $role = Role::factory()->create();
    $permissions = Permission::query()->limit(5)->pluck('id')->toArray();

    livewire(EditRole::class, [
        'record' => $role->getRouteKey(),
    ])
        ->fillForm([
            'permissions' => $permissions,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($role->refresh()->permissions->pluck('id')->toArray())
        ->toEqualCanonicalizing($permissions);
});
