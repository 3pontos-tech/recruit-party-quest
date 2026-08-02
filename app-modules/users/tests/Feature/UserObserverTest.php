<?php

declare(strict_types=1);

use He4rt\Permissions\Roles;
use He4rt\Users\User;

it('assigns the base user role on creation', function (): void {
    $user = User::factory()->create();

    expect($user->hasRole(Roles::User))->toBeTrue();
});

it('does not create a candidate profile', function (): void {
    $user = User::factory()->create();

    expect($user->candidate()->exists())->toBeFalse();
});

it('leaves the candidate relation resolvable after creation', function (): void {
    $user = User::factory()->create();
    $user->candidate()->create([]);

    expect($user->candidate)->not->toBeNull();
});
