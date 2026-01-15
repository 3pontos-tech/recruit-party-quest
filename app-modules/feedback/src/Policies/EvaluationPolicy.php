<?php

declare(strict_types=1);

namespace He4rt\Feedback\Policies;

use He4rt\Feedback\Models\Evaluation;
use He4rt\Permissions\PermissionsEnum;
use He4rt\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EvaluationPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::ViewAny->buildPermissionFor(Evaluation::class));
    }

    public function view(User $user, Evaluation $evaluation): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::View->buildPermissionFor(Evaluation::class));
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Create->buildPermissionFor(Evaluation::class));
    }

    public function update(User $user, Evaluation $evaluation): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Update->buildPermissionFor(Evaluation::class));
    }

    public function delete(User $user, Evaluation $evaluation): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Delete->buildPermissionFor(Evaluation::class));
    }

    public function restore(User $user, Evaluation $evaluation): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Restore->buildPermissionFor(Evaluation::class));
    }

    public function forceDelete(User $user, Evaluation $evaluation): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::ForceDelete->buildPermissionFor(Evaluation::class));
    }
}
