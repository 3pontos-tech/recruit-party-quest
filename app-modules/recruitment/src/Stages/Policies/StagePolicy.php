<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Stages\Policies;

use He4rt\Permissions\PermissionsEnum;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StagePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::ViewAny->buildPermissionFor(Stage::class));
    }

    public function view(User $user, Stage $stage): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::View->buildPermissionFor(Stage::class));
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Create->buildPermissionFor(Stage::class));
    }

    public function update(User $user, Stage $stage): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Update->buildPermissionFor(Stage::class));
    }

    public function delete(User $user, Stage $stage): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Delete->buildPermissionFor(Stage::class));
    }

    public function restore(User $user, Stage $stage): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Restore->buildPermissionFor(Stage::class));
    }

    public function forceDelete(User $user, Stage $stage): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::ForceDelete->buildPermissionFor(Stage::class));
    }
}
