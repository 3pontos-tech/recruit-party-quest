<?php

declare(strict_types=1);

namespace He4rt\Admin\Tests\Feature\Filament;

use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Filament\Pages\Dashboard;
use He4rt\Admin\Filament\Resources\Teams\Pages\CreateTeam;
use He4rt\Admin\Filament\Resources\Teams\Pages\EditTeam;
use He4rt\Admin\Filament\Resources\Teams\Pages\ListTeams;
use He4rt\Admin\Filament\Resources\Teams\TeamResource;
use He4rt\Permissions\Roles;
use He4rt\Teams\Team;
use He4rt\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    actingAs(User::factory()->create());

    // Sync permissions so we have some to work with
    artisan('sync:permissions');

    // Give the user SuperAdmin role to bypass all policies
    auth()->user()->assignRole(Roles::SuperAdmin->value);
});

it('can list teams with translated columns', function (): void {
    $teams = Team::factory()->count(1)->create();

    expect(__('teams::filament.fields.name'))->toBe('Name')
        ->and(__('teams::filament.fields.status'))->toBe('Status');

    livewire(ListTeams::class)
        ->assertCanSeeTableRecords($teams)
        ->assertSee(__('teams::filament.fields.name'))
        ->assertSee(__('teams::filament.fields.status'))
        ->assertSee(__('teams::filament.fields.members_count'));
});

it('can render create team page', function (): void {
    $this->get(TeamResource::getUrl('create'))
        ->assertSuccessful();
});

it('can render edit team page', function (): void {
    $team = Team::factory()->create();

    $this->get(TeamResource::getUrl('edit', ['record' => $team]))
        ->assertSuccessful();
});

it('assigns Owner role to user when creating team with owner', function (): void {
    $owner = User::factory()->create();

    livewire(CreateTeam::class)
        ->fillForm([
            'owner_id' => $owner->id,
            'name' => 'Test Team',
            'description' => 'A test team',
            'slug' => 'test-team',
            'status' => 'active',
            'contact_email' => 'team@example.com',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect($owner->refresh()->hasRole(Roles::Owner->value))->toBeTrue();
});

it('assigns Owner role to new owner and removes it from old owner when editing team owner', function (): void {
    $oldOwner = User::factory()->create();
    $oldOwner->assignRole(Roles::Owner->value);

    $newOwner = User::factory()->create();

    $team = Team::factory()->create(['owner_id' => $oldOwner->id]);

    livewire(EditTeam::class, ['record' => $team->getRouteKey()])
        ->fillForm(['owner_id' => $newOwner->id])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($newOwner->refresh()->hasRole(Roles::Owner->value))->toBeTrue()
        ->and($oldOwner->refresh()->hasRole(Roles::Owner->value))->toBeFalse();
});

it('does not remove Owner role from old owner when they still own another team', function (): void {
    $owner = User::factory()->create();
    $owner->assignRole(Roles::Owner->value);

    $newOwner = User::factory()->create();

    $team1 = Team::factory()->create(['owner_id' => $owner->id]);
    Team::factory()->create(['owner_id' => $owner->id]);

    livewire(EditTeam::class, ['record' => $team1->getRouteKey()])
        ->fillForm(['owner_id' => $newOwner->id])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($owner->refresh()->hasRole(Roles::Owner->value))->toBeTrue()
        ->and($newOwner->refresh()->hasRole(Roles::Owner->value))->toBeTrue();
});

it('removes Owner role when owner is cleared from team', function (): void {
    $owner = User::factory()->create();
    $owner->assignRole(Roles::Owner->value);

    $team = Team::factory()->create(['owner_id' => $owner->id]);

    livewire(EditTeam::class, ['record' => $team->getRouteKey()])
        ->fillForm(['owner_id' => null])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($owner->refresh()->hasRole(Roles::Owner->value))->toBeFalse();
});

it('has a manage action in the teams table', function (): void {
    $team = Team::factory()->create();

    livewire(ListTeams::class)
        ->assertActionExists(TestAction::make('manage')->table($team));
});

it('manage action redirects to the organization panel dashboard for the given team', function (): void {
    $team = Team::factory()->create();

    $expectedUrl = Dashboard::getUrl(panel: 'organization', tenant: $team);

    livewire(ListTeams::class)
        ->assertActionHasUrl(TestAction::make('manage')->table($team), $expectedUrl);
});
