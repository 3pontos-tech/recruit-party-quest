<?php

declare(strict_types=1);

namespace He4rt\Teams\Actions\NewMember;

use He4rt\Teams\Team;
use He4rt\Users\User;

class InviteTeamMemberAction
{
    public function handle(InviteTeamMemberDTO $dto): InviteTeamMemberResult
    {
        $team = Team::query()->findOrFail($dto->teamId);

        /** @var User $user */
        $user = User::query()->firstOrNew(['email' => $dto->email]);
        $isNewUser = ! $user->exists;

        if ($isNewUser) {
            $user->fill($dto->jsonSerialize())->save();
        }

        $alreadyMember = $team->members()->where('user_id', $user->getKey())->exists();

        if ($alreadyMember) {
            return InviteTeamMemberResult::AlreadyMember;
        }

        $team->members()->syncWithoutDetaching($user->getKey());

        if ($isNewUser) {
            dispatch(new SendTeamInvitationJob($user, $team));
        }

        return $isNewUser
            ? InviteTeamMemberResult::NewUserInvited
            : InviteTeamMemberResult::ExistingUserAdded;
    }
}
