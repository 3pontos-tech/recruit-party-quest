<?php

declare(strict_types=1);

use He4rt\Teams\Actions\NewMember\InviteTeamMemberAction;
use He4rt\Teams\Actions\NewMember\InviteTeamMemberDTO;
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

    $action = new InviteTeamMemberAction();
    $action->handle($dto);

    assertDatabaseHas('users', [
        'name' => 'New Member',
        'email' => 'newmember@example.com',
    ]);

    $user = User::query()->where('email', 'newmember@example.com')->first();

    expect($team->members->contains($user))->toBeTrue();

    Bus::assertDispatched(fn (SendTeamInvitationJob $job) => $job->user->is($user) && $job->team->is($team));
});
