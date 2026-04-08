<?php

declare(strict_types=1);

use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Teams\Actions\NewMember\InviteTeamMemberAction;
use He4rt\Teams\Actions\NewMember\InviteTeamMemberDTO;
use He4rt\Teams\Actions\NewMember\InviteTeamMemberResult;
use He4rt\Teams\Actions\NewMember\SendTeamInvitationJob;
use He4rt\Teams\Team;
use He4rt\Users\User;
use Illuminate\Support\Facades\Bus;

use function Pest\Laravel\assertDatabaseHas;

test('it creates a user and attaches it to a team and dispatches invitation job', function (): void {
    Bus::fake();

    $team = Team::factory()->create();
    $dto = new InviteTeamMemberDTO(
        teamId: (string) $team->id,
        name: 'New Member',
        email: 'newmember@example.com'
    );

    $result = new InviteTeamMemberAction()->handle($dto);

    expect($result)->toBe(InviteTeamMemberResult::NewUserInvited);

    assertDatabaseHas('users', [
        'name' => 'New Member',
        'email' => 'newmember@example.com',
    ]);

    $user = User::query()->where('email', 'newmember@example.com')->first();

    expect($team->members->contains($user))->toBeTrue();

    Bus::assertDispatched(fn (SendTeamInvitationJob $job) => $job->user->is($user) && $job->team->is($team));
});

test('existing user is added to team without sending an invitation e-mail', function (): void {
    Bus::fake();

    $team = Team::factory()->create();
    $existingUser = User::factory()->create(['email' => 'existing@example.com']);

    $dto = new InviteTeamMemberDTO(
        teamId: (string) $team->id,
        name: $existingUser->name,
        email: 'existing@example.com'
    );

    $result = new InviteTeamMemberAction()->handle($dto);

    expect($result)->toBe(InviteTeamMemberResult::ExistingUserAdded)
        ->and($team->fresh()->members->contains($existingUser))->toBeTrue();

    Bus::assertNotDispatched(SendTeamInvitationJob::class);
});

test('returns AlreadyMember when user is already part of the team', function (): void {
    Bus::fake();

    $team = Team::factory()->create();
    $existingUser = User::factory()->create(['email' => 'already@example.com']);
    $team->members()->syncWithoutDetaching($existingUser->getKey());

    $dto = new InviteTeamMemberDTO(
        teamId: (string) $team->id,
        name: $existingUser->name,
        email: 'already@example.com'
    );

    $result = new InviteTeamMemberAction()->handle($dto);

    expect($result)->toBe(InviteTeamMemberResult::AlreadyMember);

    Bus::assertNotDispatched(SendTeamInvitationJob::class);
});

test('inviting a new team member auto-creates a recruiter record for them', function (): void {
    Bus::fake();

    $team = Team::factory()->create();
    $dto = new InviteTeamMemberDTO(
        teamId: (string) $team->id,
        name: 'Invited Recruiter',
        email: 'recruiter@example.com'
    );

    new InviteTeamMemberAction()->handle($dto);

    $user = User::query()->where('email', 'recruiter@example.com')->first();

    assertDatabaseHas('recruiters', [
        'user_id' => $user->id,
        'team_id' => $team->id,
        'is_active' => true,
    ]);
});

test('inviting the same member twice does not create duplicate recruiter records', function (): void {
    Bus::fake();

    $team = Team::factory()->create();
    $dto = new InviteTeamMemberDTO(
        teamId: (string) $team->id,
        name: 'Invited Recruiter',
        email: 'recruiter-dupe@example.com'
    );

    new InviteTeamMemberAction()->handle($dto);

    $user = User::query()->where('email', 'recruiter-dupe@example.com')->first();

    Recruiter::query()->firstOrCreate(
        ['user_id' => $user->id, 'team_id' => $team->id],
        ['is_active' => true]
    );

    expect(Recruiter::query()->where('user_id', $user->id)->where('team_id', $team->id)->count())->toBe(1);
});
