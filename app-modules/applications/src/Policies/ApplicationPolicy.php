<?php

declare(strict_types=1);

namespace He4rt\Applications\Policies;

use He4rt\Applications\Models\Application;
use He4rt\Permissions\PermissionsEnum;
use He4rt\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApplicationPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::ViewAny->buildPermissionFor(Application::class));
    }

    public function view(User $user, Application $application): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::View->buildPermissionFor(Application::class));
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Create->buildPermissionFor(Application::class));
    }

    public function update(User $user, Application $application): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Update->buildPermissionFor(Application::class));
    }

    public function delete(User $user, Application $application): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Delete->buildPermissionFor(Application::class));
    }

    public function restore(User $user, Application $application): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Restore->buildPermissionFor(Application::class));
    }

    public function forceDelete(User $user, Application $application): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::ForceDelete->buildPermissionFor(Application::class));
    }
}
