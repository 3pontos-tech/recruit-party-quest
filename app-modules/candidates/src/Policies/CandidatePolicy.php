<?php

declare(strict_types=1);

namespace He4rt\Candidates\Policies;

use He4rt\Candidates\Models\Candidate;
use He4rt\Permissions\PermissionsEnum;
use He4rt\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CandidatePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::ViewAny->buildPermissionFor(Candidate::class));
    }

    public function view(User $user, Candidate $candidate): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::View->buildPermissionFor(Candidate::class));
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Create->buildPermissionFor(Candidate::class));
    }

    public function update(User $user, Candidate $candidate): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Update->buildPermissionFor(Candidate::class));
    }

    public function delete(User $user, Candidate $candidate): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Delete->buildPermissionFor(Candidate::class));
    }

    public function restore(User $user, Candidate $candidate): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Restore->buildPermissionFor(Candidate::class));
    }

    public function forceDelete(User $user, Candidate $candidate): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::ForceDelete->buildPermissionFor(Candidate::class));
    }
}
