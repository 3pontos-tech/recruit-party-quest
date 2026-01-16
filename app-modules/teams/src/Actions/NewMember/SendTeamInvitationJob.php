<?php

declare(strict_types=1);

namespace He4rt\Teams\Actions\NewMember;

use He4rt\Teams\Team;
use He4rt\Users\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendTeamInvitationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $user,
        public Team $team,
    ) {}

    public function handle(): void
    {
        $this->user->notify(new InviteTeamMemberNotification(
            user: $this->user,
            team: $this->team,
        ));
    }
}
