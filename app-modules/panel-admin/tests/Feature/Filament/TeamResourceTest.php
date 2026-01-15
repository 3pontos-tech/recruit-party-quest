<?php

declare(strict_types=1);

namespace He4rt\Admin\Tests\Feature\Filament;

use Filament\Facades\Filament;
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
