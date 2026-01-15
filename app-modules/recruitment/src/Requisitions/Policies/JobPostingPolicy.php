<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Requisitions\Policies;

use He4rt\Permissions\PermissionsEnum;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class JobPostingPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::ViewAny->buildPermissionFor(JobPosting::class));
    }

    public function view(User $user, JobPosting $jobPosting): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::View->buildPermissionFor(JobPosting::class));
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Create->buildPermissionFor(JobPosting::class));
    }

    public function update(User $user, JobPosting $jobPosting): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Update->buildPermissionFor(JobPosting::class));
    }

    public function delete(User $user, JobPosting $jobPosting): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Delete->buildPermissionFor(JobPosting::class));
    }

    public function restore(User $user, JobPosting $jobPosting): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Restore->buildPermissionFor(JobPosting::class));
    }

    public function forceDelete(User $user, JobPosting $jobPosting): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::ForceDelete->buildPermissionFor(JobPosting::class));
    }
}
