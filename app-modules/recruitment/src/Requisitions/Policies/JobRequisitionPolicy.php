<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Recruitment\Policies;

use He4rt\Permissions\PermissionsEnum;
use He4rt\Recruitment\Recruitment\Models\JobRequisition;
use He4rt\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class JobRequisitionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::ViewAny->buildPermissionFor(JobRequisition::class));
    }

    public function view(User $user, JobRequisition $jobRequisition): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::View->buildPermissionFor(JobRequisition::class));
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Create->buildPermissionFor(JobRequisition::class));
    }

    public function update(User $user, JobRequisition $jobRequisition): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Update->buildPermissionFor(JobRequisition::class));
    }

    public function delete(User $user, JobRequisition $jobRequisition): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Delete->buildPermissionFor(JobRequisition::class));
    }

    public function restore(User $user, JobRequisition $jobRequisition): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Restore->buildPermissionFor(JobRequisition::class));
    }

    public function forceDelete(User $user, JobRequisition $jobRequisition): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::ForceDelete->buildPermissionFor(JobRequisition::class));
    }
}
