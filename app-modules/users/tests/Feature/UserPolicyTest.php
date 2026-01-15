<?php

declare(strict_types=1);

namespace He4rt\Users\Tests\Feature;

use He4rt\Permissions\Roles;
use He4rt\Users\User;
use Illuminate\Support\Facades\Gate;

use function Pest\Laravel\artisan;

beforeEach(function (): void {
    artisan('sync:permissions');
});

it('allows super admin to perform all actions', function (): void {
    $superUser = User::factory()->create();
    $superUser->assignRole(Roles::SuperAdmin->value);

    $user = User::factory()->create();

    expect(Gate::forUser($superUser)->allows('viewAny', User::class))->toBeTrue()
        ->and(Gate::forUser($superUser)->allows('view', $user))->toBeTrue()
        ->and(Gate::forUser($superUser)->allows('create', User::class))->toBeTrue()
        ->and(Gate::forUser($superUser)->allows('update', $user))->toBeTrue()
        ->and(Gate::forUser($superUser)->allows('delete', $user))->toBeTrue()
        ->and(Gate::forUser($superUser)->allows('restore', $user))->toBeTrue()
        ->and(Gate::forUser($superUser)->allows('forceDelete', $user))->toBeTrue();
});

it('denies non-super admin to perform collection and other user actions', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    expect(Gate::forUser($user)->denies('viewAny', User::class))->toBeTrue()
        ->and(Gate::forUser($user)->denies('view', $otherUser))->toBeTrue()
        ->and(Gate::forUser($user)->denies('create', User::class))->toBeTrue()
        ->and(Gate::forUser($user)->denies('update', $otherUser))->toBeTrue()
        ->and(Gate::forUser($user)->denies('delete', $otherUser))->toBeTrue()
        ->and(Gate::forUser($user)->denies('restore', $otherUser))->toBeTrue()
        ->and(Gate::forUser($user)->denies('forceDelete', $otherUser))->toBeTrue();
});

it('allows non-super admin to update themselves', function (): void {
    $user = User::factory()->create();

    expect(Gate::forUser($user)->allows('update', $user))->toBeTrue();
});
