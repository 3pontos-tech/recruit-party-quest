<?php

declare(strict_types=1);

namespace He4rt\Users\Tests\Feature;

use Filament\Panel;
use He4rt\Permissions\Roles;
use He4rt\Users\User;
use Mockery;

use function Pest\Laravel\artisan;

beforeEach(function (): void {
    artisan('sync:permissions');
});

it('allows super admin to access all panels', function (string $panelId): void {
    $user = User::factory()->create();
    $user->assignRole(Roles::SuperAdmin->value);

    $panel = Mockery::mock(Panel::class);
    $panel->shouldReceive('getId')->andReturn($panelId);

    expect($user->canAccessPanel($panel))->toBeTrue();
})->with(['admin', 'organization', 'app']);

it('allows admin to access all panels', function (string $panelId): void {
    $user = User::factory()->create();
    $user->assignRole(Roles::Admin->value);

    $panel = Mockery::mock(Panel::class);
    $panel->shouldReceive('getId')->andReturn($panelId);

    expect($user->canAccessPanel($panel))->toBeTrue();
})->with(['admin', 'organization', 'app']);

it('denies owner access to admin panel', function (): void {
    $user = User::factory()->create();
    $user->assignRole(Roles::Owner->value);

    $panel = Mockery::mock(Panel::class);
    $panel->shouldReceive('getId')->andReturn('admin');

    expect($user->canAccessPanel($panel))->toBeFalse();
});

it('allows owner to access organization and app panels', function (string $panelId): void {
    $user = User::factory()->create();
    $user->assignRole(Roles::Owner->value);

    $panel = Mockery::mock(Panel::class);
    $panel->shouldReceive('getId')->andReturn($panelId);

    expect($user->canAccessPanel($panel))->toBeTrue();
})->with(['organization', 'app']);

it('denies user access to admin and organization panels', function (string $panelId): void {
    $user = User::factory()->create();
    $user->assignRole(Roles::User->value);

    $panel = Mockery::mock(Panel::class);
    $panel->shouldReceive('getId')->andReturn($panelId);

    expect($user->canAccessPanel($panel))->toBeFalse();
})->with(['admin', 'organization']);

it('allows user to access app panel', function (): void {
    $user = User::factory()->create();
    $user->assignRole(Roles::User->value);

    $panel = Mockery::mock(Panel::class);
    $panel->shouldReceive('getId')->andReturn('app');

    expect($user->canAccessPanel($panel))->toBeTrue();
});
