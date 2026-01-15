<?php

declare(strict_types=1);

namespace He4rt\Teams\Policies;

use He4rt\Permissions\PermissionsEnum;
use He4rt\Teams\Department;
use He4rt\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DepartmentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::ViewAny->buildPermissionFor(Department::class));
    }

    public function view(User $user, Department $department): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::View->buildPermissionFor(Department::class));
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Create->buildPermissionFor(Department::class));
    }

    public function update(User $user, Department $department): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Update->buildPermissionFor(Department::class));
    }

    public function delete(User $user, Department $department): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Delete->buildPermissionFor(Department::class));
    }

    public function restore(User $user, Department $department): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Restore->buildPermissionFor(Department::class));
    }

    public function forceDelete(User $user, Department $department): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::ForceDelete->buildPermissionFor(Department::class));
    }
}
