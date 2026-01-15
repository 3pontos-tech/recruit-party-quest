<?php

declare(strict_types=1);

namespace He4rt\Feedback\Policies;

use He4rt\Feedback\Models\ApplicationComment;
use He4rt\Permissions\PermissionsEnum;
use He4rt\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApplicationCommentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::ViewAny->buildPermissionFor(ApplicationComment::class));
    }

    public function view(User $user, ApplicationComment $applicationComment): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::View->buildPermissionFor(ApplicationComment::class));
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Create->buildPermissionFor(ApplicationComment::class));
    }

    public function update(User $user, ApplicationComment $applicationComment): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Update->buildPermissionFor(ApplicationComment::class));
    }

    public function delete(User $user, ApplicationComment $applicationComment): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Delete->buildPermissionFor(ApplicationComment::class));
    }

    public function restore(User $user, ApplicationComment $applicationComment): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Restore->buildPermissionFor(ApplicationComment::class));
    }

    public function forceDelete(User $user, ApplicationComment $applicationComment): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::ForceDelete->buildPermissionFor(ApplicationComment::class));
    }
}
