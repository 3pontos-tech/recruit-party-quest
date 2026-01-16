<?php

declare(strict_types=1);

use He4rt\Teams\Actions\NewMember\TeamInvitationMail;
use He4rt\Teams\Team;
use He4rt\Users\User;

test('it has the correct subject', function (): void {
    $user = User::factory()->make(['name' => 'John Doe']);
    $team = Team::factory()->make(['name' => 'Core Team']);

    $mailable = new TeamInvitationMail($user, $team);

    expect($mailable->envelope()->subject)->toBe(__('teams::filament.emails.team_invitation.subject', [
        'team_name' => 'Core Team',
    ]));
});

test('it has the correct content', function (): void {
    $user = User::factory()->make();
    $team = Team::factory()->make();

    $mailable = new TeamInvitationMail($user, $team);

    $content = $mailable->content();

    expect($content->markdown)->toBe('teams::emails.team-invitation')
        ->and($content->with['user'])->toBe($user)
        ->and($content->with['team'])->toBe($team);
});

test('it can be rendered', function (): void {
    $user = User::factory()->make();
    $team = Team::factory()->make();

    $mailable = new TeamInvitationMail($user, $team);

    expect($mailable->render())->toBeString();
});
