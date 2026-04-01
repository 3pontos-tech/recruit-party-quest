<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\Organization\Filament\Pages\Tenancy\RegisterTenant;
use He4rt\Permissions\Roles;
use He4rt\Teams\Team;
use He4rt\Teams\TeamStatus;
use He4rt\Users\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function (): void {
    filament()->setCurrentPanel(FilamentPanel::Organization->value);
});

/** @return array<string, mixed> */
function validRegistrationData(): array
{
    return [
        'name' => 'Acme Corp',
        'description' => 'A great company',
        'slug' => 'acme-corp',
        'contact_email' => 'contact@acme.com',
    ];
}

it('sets owner_id to the authenticated user on registration', function (): void {
    $user = User::factory()->create();
    $user->assignRole(Roles::SuperAdmin->value);
    actingAs($user);

    Livewire::test(RegisterTenant::class)
        ->fillForm(validRegistrationData())
        ->call('register');

    assertDatabaseHas('teams', [
        'name' => 'Acme Corp',
        'owner_id' => $user->id,
    ]);
});

it('creates the team with the submitted name', function (): void {
    $user = User::factory()->create();
    $user->assignRole(Roles::SuperAdmin->value);
    actingAs($user);

    Livewire::test(RegisterTenant::class)
        ->fillForm(validRegistrationData())
        ->call('register');

    $team = Team::query()->where('owner_id', $user->id)->first();

    expect($team)->not->toBeNull()
        ->and($team->name)->toBe('Acme Corp');
});

it('attaches the authenticated user as a team member immediately after registration', function (): void {
    $user = User::factory()->create();
    $user->assignRole(Roles::SuperAdmin->value);
    actingAs($user);

    Livewire::test(RegisterTenant::class)
        ->fillForm(validRegistrationData())
        ->call('register');

    $team = Team::query()->where('owner_id', $user->id)->first();

    assertDatabaseHas('team_user', [
        'team_id' => $team->id,
        'user_id' => $user->id,
    ]);
});

it('returns 404 for unauthenticated users trying to access the registration page', function (): void {
    Livewire::test(RegisterTenant::class)
        ->assertStatus(404);
});

it('sets status to active automatically on registration', function (): void {
    $user = User::factory()->create();
    $user->assignRole(Roles::SuperAdmin->value);
    actingAs($user);

    Livewire::test(RegisterTenant::class)
        ->fillForm(validRegistrationData())
        ->call('register');

    $team = Team::query()->where('owner_id', $user->id)->first();

    expect($team->status)->toBe(TeamStatus::Active);
});
