<?php

declare(strict_types=1);

namespace He4rt\Admin\Tests\Feature\Filament;

use App\Enums\FilamentPanel;
use Filament\Actions\Testing\TestAction;
use He4rt\Admin\Filament\Resources\Teams\Pages\EditTeam;
use He4rt\Admin\Filament\Resources\Teams\RelationManagers\MembersRelationManager;
use He4rt\Teams\Actions\NewMember\SendTeamInvitationJob;
use He4rt\Teams\Team;
use He4rt\Users\User;
use Illuminate\Support\Facades\Bus;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    filament()->setCurrentPanel(FilamentPanel::Admin->value);

    actingAs(User::factory()->admin()->create());
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

test('invite action adds existing user to team without dispatching job', function (): void {
    Bus::fake();

    $team = Team::factory()->create();
    $existingUser = User::factory()->create(['email' => 'existing@example.com']);

    livewire(MembersRelationManager::class, [
        'ownerRecord' => $team,
        'pageClass' => EditTeam::class,
    ])
        ->callAction(TestAction::make('invite')->table(), data: [
            'name' => $existingUser->name,
            'email' => 'existing@example.com',
        ])
        ->assertHasNoActionErrors()
        ->assertNotified(__('teams::filament.relation_managers.members.invite_success'));

    expect($team->fresh()->members->contains($existingUser))->toBeTrue();

    Bus::assertNotDispatched(SendTeamInvitationJob::class);
});

test('invite action shows warning and halts when user is already a member', function (): void {
    Bus::fake();

    $team = Team::factory()->create();
    $existingMember = User::factory()->create(['email' => 'member@example.com']);
    $team->members()->syncWithoutDetaching($existingMember->getKey());

    livewire(MembersRelationManager::class, [
        'ownerRecord' => $team,
        'pageClass' => EditTeam::class,
    ])
        ->callAction(TestAction::make('invite')->table(), data: [
            'name' => $existingMember->name,
            'email' => 'member@example.com',
        ])
        ->assertActionHalted(TestAction::make('invite')->table())
        ->assertNotified(__('teams::filament.relation_managers.members.invite_already_member'));

    Bus::assertNotDispatched(SendTeamInvitationJob::class);
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
