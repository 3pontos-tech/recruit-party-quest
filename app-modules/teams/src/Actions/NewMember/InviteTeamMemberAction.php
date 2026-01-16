<?php

declare(strict_types=1);

namespace He4rt\Teams\Actions\NewMember;

use He4rt\Teams\Team;
use He4rt\Users\User;

class InviteTeamMemberAction
{
    public function handle(InviteTeamMemberDTO $inviteTeamMemberDTO): void
    {
        $user = User::query()->create($inviteTeamMemberDTO->jsonSerialize());

        $team = Team::query()->findOrFail($inviteTeamMemberDTO->teamId);

        $team->members()->syncWithoutDetaching($user->getKey());

        dispatch(new SendTeamInvitationJob($user, $team));
    }
}
