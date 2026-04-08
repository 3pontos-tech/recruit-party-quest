<?php

declare(strict_types=1);

namespace He4rt\Teams\Actions\NewMember;

enum InviteTeamMemberResult
{
    /** New user was created, added to the team, and an invitation e-mail was dispatched. */
    case NewUserInvited;

    /** An existing user was added to the team. No e-mail was sent. */
    case ExistingUserAdded;

    /** The user was already a member of the team. No changes were made. */
    case AlreadyMember;
}
