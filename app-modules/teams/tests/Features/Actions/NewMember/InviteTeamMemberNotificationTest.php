<?php

declare(strict_types=1);

use He4rt\Teams\Actions\NewMember\InviteTeamMemberNotification;
use He4rt\Teams\Actions\NewMember\TeamInvitationMail;
use He4rt\Teams\Team;
use He4rt\Users\User;

test('it has the correct delivery channels', function (): void {
    $user = User::factory()->make();
    $team = Team::factory()->make();
    $notification = new InviteTeamMemberNotification($user, $team);

    expect($notification->via($user))->toBe(['mail']);
});

test('it returns the correct mailable', function (): void {
    $user = User::factory()->make();
    $team = Team::factory()->make();
    $notification = new InviteTeamMemberNotification($user, $team);

    $mailable = $notification->toMail($user);

    expect($mailable)->toBeInstanceOf(TeamInvitationMail::class)
        ->and($mailable->user)->toBe($user)
        ->and($mailable->team)->toBe($team)
        ->and($mailable->hasTo($user->email))->toBeTrue();
});
