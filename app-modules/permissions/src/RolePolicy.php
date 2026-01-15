<?php

declare(strict_types=1);

namespace He4rt\Permissions;

use He4rt\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole(Roles::SuperAdmin);
    }

    public function view(User $user, Role $role): bool
    {

        return $user->hasRole(Roles::SuperAdmin);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Roles::SuperAdmin);
    }

    public function update(User $user, Role $role): bool
    {
        return $user->hasRole(Roles::SuperAdmin);
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->hasRole(Roles::SuperAdmin);
    }

    public function restore(User $user, Role $role): bool
    {
        return $user->hasRole(Roles::SuperAdmin);
    }

    public function forceDelete(User $user, Role $role): bool
    {
        return $user->hasRole(Roles::SuperAdmin);
    }
}
