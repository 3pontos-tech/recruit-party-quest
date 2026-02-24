<?php

declare(strict_types=1);

namespace He4rt\Users\Tests\Feature;

use App\Enums\FilamentPanel;
use Filament\Panel;
use He4rt\Permissions\Roles;
use He4rt\Users\User;
use Mockery;

use function Pest\Laravel\artisan;

beforeEach(function (): void {
    artisan('sync:permissions');
});

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
