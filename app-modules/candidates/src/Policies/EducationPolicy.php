<?php

declare(strict_types=1);

namespace He4rt\Candidates\Policies;

use He4rt\Candidates\Models\Education;
use He4rt\Permissions\PermissionsEnum;
use He4rt\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EducationPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::ViewAny->buildPermissionFor(Education::class));
    }

    public function view(User $user, Education $education): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::View->buildPermissionFor(Education::class));
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Create->buildPermissionFor(Education::class));
    }

    public function update(User $user, Education $education): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Update->buildPermissionFor(Education::class));
    }

    public function delete(User $user, Education $education): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Delete->buildPermissionFor(Education::class));
    }

    public function restore(User $user, Education $education): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Restore->buildPermissionFor(Education::class));
    }

    public function forceDelete(User $user, Education $education): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::ForceDelete->buildPermissionFor(Education::class));
    }
}
