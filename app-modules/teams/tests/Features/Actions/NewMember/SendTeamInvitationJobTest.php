<?php

declare(strict_types=1);

use He4rt\Teams\Actions\NewMember\InviteTeamMemberNotification;
use He4rt\Teams\Actions\NewMember\SendTeamInvitationJob;
use He4rt\Teams\Team;
use He4rt\Users\User;
use Illuminate\Support\Facades\Notification;

test('it sends the team invitation notification', function (): void {
    Notification::fake();

    $user = User::factory()->create();
    $team = Team::factory()->create();

    $job = new SendTeamInvitationJob($user, $team);
    $job->handle();

    Notification::assertSentTo($user, InviteTeamMemberNotification::class, fn (InviteTeamMemberNotification $notification) => $notification->user->is($user)
           && $notification->team->is($team));
});
