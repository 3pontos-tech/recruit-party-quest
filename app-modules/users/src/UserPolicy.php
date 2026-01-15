<?php

declare(strict_types=1);

namespace He4rt\Users;

use He4rt\Permissions\Roles;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole(Roles::SuperAdmin);
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasRole(Roles::SuperAdmin);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Roles::SuperAdmin);
    }

    public function update(User $user, User $model): bool
    {
        if ($user->hasRole(Roles::SuperAdmin)) {
            return true;
        }

        return $user->id === $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasRole(Roles::SuperAdmin);
    }

    public function restore(User $user, User $model): bool
    {
        return $user->hasRole(Roles::SuperAdmin);
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $user->hasRole(Roles::SuperAdmin);
    }
}
