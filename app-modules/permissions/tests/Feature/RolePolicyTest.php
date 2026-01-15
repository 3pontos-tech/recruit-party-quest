<?php

declare(strict_types=1);

namespace He4rt\Permissions\Tests\Feature;

use He4rt\Permissions\Role;
use He4rt\Permissions\Roles;
use He4rt\Users\User;
use Illuminate\Support\Facades\Gate;

use function Pest\Laravel\artisan;

beforeEach(function (): void {
    artisan('sync:permissions');
});

it('allows super admin to perform all actions', function (): void {
    $user = User::factory()->create();
    $user->assignRole(Roles::SuperAdmin->value);

    $role = Role::factory()->create();

    expect(Gate::forUser($user)->allows('viewAny', Role::class))->toBeTrue()
        ->and(Gate::forUser($user)->allows('view', $role))->toBeTrue()
        ->and(Gate::forUser($user)->allows('create', Role::class))->toBeTrue()
        ->and(Gate::forUser($user)->allows('update', $role))->toBeTrue()
        ->and(Gate::forUser($user)->allows('delete', $role))->toBeTrue()
        ->and(Gate::forUser($user)->allows('restore', $role))->toBeTrue()
        ->and(Gate::forUser($user)->allows('forceDelete', $role))->toBeTrue();
});

it('denies non-super admin to perform any action', function (): void {
    $user = User::factory()->create();
    // Do not assign SuperAdmin role

    $role = Role::factory()->create();

    expect(Gate::forUser($user)->denies('viewAny', Role::class))->toBeTrue()
        ->and(Gate::forUser($user)->denies('view', $role))->toBeTrue()
        ->and(Gate::forUser($user)->denies('create', Role::class))->toBeTrue()
        ->and(Gate::forUser($user)->denies('update', $role))->toBeTrue()
        ->and(Gate::forUser($user)->denies('delete', $role))->toBeTrue()
        ->and(Gate::forUser($user)->denies('restore', $role))->toBeTrue()
        ->and(Gate::forUser($user)->denies('forceDelete', $role))->toBeTrue();
});
