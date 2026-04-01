<?php

declare(strict_types=1);

namespace He4rt\Admin\Tests\Feature\Filament;

use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use He4rt\Admin\Filament\Resources\Teams\Pages\EditTeam;
use He4rt\Admin\Filament\Resources\Teams\RelationManagers\MembersRelationManager;
use He4rt\Permissions\Roles;
use He4rt\Teams\Actions\NewMember\SendTeamInvitationJob;
use He4rt\Teams\Team;
use He4rt\Users\User;
use Illuminate\Support\Facades\Bus;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    actingAs(User::factory()->create());

    auth()->user()->assignRole(Roles::SuperAdmin->value);
});

test('invite action creates user, attaches to team and dispatches job', function (): void {
    Bus::fake();

    $team = Team::factory()->create();

    livewire(MembersRelationManager::class, [
        'ownerRecord' => $team,
        'pageClass' => EditTeam::class,
    ])
        ->callAction(TestAction::make('invite')->table(), data: [
            'name' => 'New Member',
            'email' => 'new@example.com',
        ])
        ->assertHasNoActionErrors()
        ->assertNotified(__('teams::filament.relation_managers.members.invite_success'));

    assertDatabaseHas('users', ['email' => 'new@example.com']);

    $user = User::query()->where('email', 'new@example.com')->firstOrFail();

    expect($team->members->contains($user))->toBeTrue();

    Bus::assertDispatched(
        fn (SendTeamInvitationJob $job) => $job->user->is($user) && $job->team->is($team)
    );
});

test('invite modal does not expose a password field', function (): void {
    $team = Team::factory()->create();

    livewire(MembersRelationManager::class, [
        'ownerRecord' => $team,
        'pageClass' => EditTeam::class,
    ])
        ->mountAction(TestAction::make('invite')->table())
        ->assertSchemaComponentDoesNotExist('password');
});

test('invite action validates required fields', function (): void {
    $team = Team::factory()->create();

    livewire(MembersRelationManager::class, [
        'ownerRecord' => $team,
        'pageClass' => EditTeam::class,
    ])
        ->callAction(TestAction::make('invite')->table(), data: ['name' => '', 'email' => ''])
        ->assertHasActionErrors(['name' => 'required', 'email' => 'required']);
});

test('invite action validates email format', function (): void {
    $team = Team::factory()->create();

    livewire(MembersRelationManager::class, [
        'ownerRecord' => $team,
        'pageClass' => EditTeam::class,
    ])
        ->callAction(TestAction::make('invite')->table(), data: ['name' => 'Foo', 'email' => 'not-an-email'])
        ->assertHasActionErrors(['email' => 'email']);
});
