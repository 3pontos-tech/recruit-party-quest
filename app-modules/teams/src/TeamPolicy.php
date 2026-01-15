<?php

declare(strict_types=1);

namespace He4rt\Teams;

use He4rt\Permissions\Roles;
use He4rt\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TeamPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Team $team): bool
    {
        if ($user->hasRole(Roles::SuperAdmin)) {
            return true;
        }

        return $user->id === $team->owner_id || $user->teams()->whereKey($team->getKey())->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Team $team): bool
    {
        if ($user->hasRole(Roles::SuperAdmin) || $user->id === $team->owner_id) {
            return true;
        }

        return $user->id === $team->owner_id;
    }

    public function delete(User $user, Team $team): bool
    {
        if ($user->hasRole(Roles::SuperAdmin)) {
            return true;
        }

        return $user->id === $team->owner_id;
    }

    public function restore(User $user, Team $team): bool
    {
        return $user->hasRole(Roles::SuperAdmin);
    }

    public function forceDelete(User $user, Team $team): bool
    {
        return $user->hasRole(Roles::SuperAdmin);
    }
}
