<?php

declare(strict_types=1);

namespace He4rt\Candidates\Policies;

use He4rt\Candidates\Models\Skill;
use He4rt\Permissions\PermissionsEnum;
use He4rt\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SkillPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::ViewAny->buildPermissionFor(Skill::class));
    }

    public function view(User $user, Skill $skill): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::View->buildPermissionFor(Skill::class));
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Create->buildPermissionFor(Skill::class));
    }

    public function update(User $user, Skill $skill): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Update->buildPermissionFor(Skill::class));
    }

    public function delete(User $user, Skill $skill): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Delete->buildPermissionFor(Skill::class));
    }

    public function restore(User $user, Skill $skill): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Restore->buildPermissionFor(Skill::class));
    }

    public function forceDelete(User $user, Skill $skill): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::ForceDelete->buildPermissionFor(Skill::class));
    }
}
