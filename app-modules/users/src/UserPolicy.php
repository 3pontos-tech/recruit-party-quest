<?php

declare(strict_types=1);

namespace He4rt\Users;

use He4rt\Permissions\PermissionsEnum;
use He4rt\Permissions\Roles;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        if ($user->hasRole(Roles::SuperAdmin)) {
            return true;
        }

        return $user->hasPermissionTo(PermissionsEnum::ViewAny->buildPermissionFor(User::class));
    }

    public function view(User $user, User $model): bool
    {
        if ($user->hasRole(Roles::SuperAdmin)) {
            return true;
        }

        return $user->hasPermissionTo(PermissionsEnum::View->buildPermissionFor(User::class));
    }

    public function create(User $user): bool
    {
        if ($user->hasRole(Roles::SuperAdmin)) {
            return true;
        }

        return $user->hasPermissionTo(PermissionsEnum::Create->buildPermissionFor(User::class));
    }

    public function update(User $user, User $model): bool
    {
        if ($user->hasPermissionTo(PermissionsEnum::Update->buildPermissionFor(User::class))) {
            return true;
        }

        return $user->id === $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        if ($user->hasRole(Roles::SuperAdmin)) {
            return true;
        }

        return $user->hasPermissionTo(PermissionsEnum::Delete->buildPermissionFor(User::class));
    }

    public function restore(User $user, User $model): bool
    {
        if ($user->hasRole(Roles::SuperAdmin)) {
            return true;
        }

        return $user->hasPermissionTo(PermissionsEnum::Restore->buildPermissionFor(User::class));
    }

    public function forceDelete(User $user, User $model): bool
    {
        if ($user->hasRole(Roles::SuperAdmin)) {
            return true;
        }

        return $user->hasPermissionTo(PermissionsEnum::ForceDelete->buildPermissionFor(User::class));
    }
}
