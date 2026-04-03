<?php

declare(strict_types=1);

namespace He4rt\Permissions\Tests\Feature;

use He4rt\Permissions\Role;
use He4rt\Users\User;
use Illuminate\Support\Facades\Gate;

beforeEach(function (): void {
    $this->role = Role::factory()->create();
});

it('allows super admin to perform all actions', function (): void {
    $user = User::factory()->admin()->create();

    expect(Gate::forUser($user)->allows('viewAny', Role::class))->toBeTrue()
        ->and(Gate::forUser($user)->allows('view', $this->role))->toBeTrue()
        ->and(Gate::forUser($user)->allows('create', Role::class))->toBeTrue()
        ->and(Gate::forUser($user)->allows('update', $this->role))->toBeTrue()
        ->and(Gate::forUser($user)->allows('delete', $this->role))->toBeTrue()
        ->and(Gate::forUser($user)->allows('restore', $this->role))->toBeTrue()
        ->and(Gate::forUser($user)->allows('forceDelete', $this->role))->toBeTrue();
});

it('denies non-super admin to perform any action', function (): void {
    $user = User::factory()->create();

    expect(Gate::forUser($user)->denies('viewAny', Role::class))->toBeTrue()
        ->and(Gate::forUser($user)->denies('view', $this->role))->toBeTrue()
        ->and(Gate::forUser($user)->denies('create', Role::class))->toBeTrue()
        ->and(Gate::forUser($user)->denies('update', $this->role))->toBeTrue()
        ->and(Gate::forUser($user)->denies('delete', $this->role))->toBeTrue()
        ->and(Gate::forUser($user)->denies('restore', $this->role))->toBeTrue()
        ->and(Gate::forUser($user)->denies('forceDelete', $this->role))->toBeTrue();
});
