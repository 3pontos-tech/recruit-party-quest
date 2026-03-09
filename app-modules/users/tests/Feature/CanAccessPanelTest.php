<?php

declare(strict_types=1);

namespace He4rt\Users\Tests\Feature;

use App\Enums\FilamentPanel;
use Filament\Panel;
use He4rt\Permissions\Roles;
use He4rt\Teams\Team;
use He4rt\Users\User;
use Mockery;

function panelFor(FilamentPanel $panel): Panel
{
    $mock = Mockery::mock(Panel::class);
    $mock->shouldReceive('currentPanel')->andReturn($panel);

    return $mock;
}

it('allows super admin to access all panels', function (FilamentPanel $panel): void {
    $user = User::factory()->create();
    $user->assignRole(Roles::SuperAdmin->value);

    expect($user->canAccessPanel(panelFor($panel)))->toBeTrue();
})->with([FilamentPanel::Admin, FilamentPanel::Organization, FilamentPanel::App]);

it('allows admin to access all panels', function (FilamentPanel $panel): void {
    $user = User::factory()->create();
    $user->assignRole(Roles::Admin->value);

    expect($user->canAccessPanel(panelFor($panel)))->toBeTrue();
})->with([FilamentPanel::Admin, FilamentPanel::Organization, FilamentPanel::App]);

it('denies owner access to admin panel', function (): void {
    $user = User::factory()->create();
    $user->assignRole(Roles::Owner->value);

    expect($user->canAccessPanel(panelFor(FilamentPanel::Admin)))->toBeFalse();
});

it('allows owner to access organization and app panels', function (FilamentPanel $panel): void {
    $user = User::factory()->create();
    $user->assignRole(Roles::Owner->value);

    expect($user->canAccessPanel(panelFor($panel)))->toBeTrue();
})->with([FilamentPanel::Organization, FilamentPanel::App]);

it('denies user access to admin and organization panels', function (FilamentPanel $panel): void {
    $user = User::factory()->create();
    $user->assignRole(Roles::User->value);

    expect($user->canAccessPanel(panelFor($panel)))->toBeFalse();
})->with([FilamentPanel::Admin, FilamentPanel::Organization]);

it('allows user to access app panel', function (): void {
    $user = User::factory()->create();
    $user->assignRole(Roles::User->value);

    expect($user->canAccessPanel(panelFor(FilamentPanel::App)))->toBeTrue();
});

it('returns owned team in getTenants for owner', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);

    $tenants = $user->getTenants(panelFor(FilamentPanel::Organization));

    expect($tenants->contains('id', $team->id))->toBeTrue();
});

it('allows owner to access their owned team via canAccessTenant', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);

    expect($user->canAccessTenant($team))->toBeTrue();
});

it('returns all teams in getTenants for admin', function (Roles $role): void {
    $admin = User::factory()->create();
    $admin->assignRole($role->value);

    Team::factory()->count(3)->create();

    $tenants = $admin->getTenants(panelFor(FilamentPanel::Organization));

    expect($tenants->count())->toBe(Team::query()->count());
})->with([Roles::SuperAdmin, Roles::Admin]);

it('allows admin to access any team via canAccessTenant', function (Roles $role): void {
    $admin = User::factory()->create();
    $admin->assignRole($role->value);

    $team = Team::factory()->create();

    expect($admin->canAccessTenant($team))->toBeTrue();
})->with([Roles::SuperAdmin, Roles::Admin]);

it('does not duplicate team in getTenants when user is both owner and member', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $user->teams()->attach($team);

    $tenants = $user->getTenants(panelFor(FilamentPanel::Organization));

    expect($tenants->where('id', $team->id)->count())->toBe(1);
});
