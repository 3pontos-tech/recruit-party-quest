<?php

declare(strict_types=1);

namespace He4rt\Candidates\Policies;

use He4rt\Candidates\Models\WorkExperience;
use He4rt\Permissions\PermissionsEnum;
use He4rt\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkExperiencePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::ViewAny->buildPermissionFor(WorkExperience::class));
    }

    public function view(User $user, WorkExperience $workExperience): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::View->buildPermissionFor(WorkExperience::class));
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Create->buildPermissionFor(WorkExperience::class));
    }

    public function update(User $user, WorkExperience $workExperience): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Update->buildPermissionFor(WorkExperience::class));
    }

    public function delete(User $user, WorkExperience $workExperience): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Delete->buildPermissionFor(WorkExperience::class));
    }

    public function restore(User $user, WorkExperience $workExperience): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Restore->buildPermissionFor(WorkExperience::class));
    }

    public function forceDelete(User $user, WorkExperience $workExperience): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::ForceDelete->buildPermissionFor(WorkExperience::class));
    }
}
