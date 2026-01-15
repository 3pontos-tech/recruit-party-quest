<?php

declare(strict_types=1);

namespace He4rt\Applications\Policies;

use He4rt\Applications\Models\ApplicationStageHistory;
use He4rt\Permissions\PermissionsEnum;
use He4rt\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApplicationStageHistoryPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::ViewAny->buildPermissionFor(ApplicationStageHistory::class));
    }

    public function view(User $user, ApplicationStageHistory $history): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::View->buildPermissionFor(ApplicationStageHistory::class));
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Create->buildPermissionFor(ApplicationStageHistory::class));
    }

    public function update(User $user, ApplicationStageHistory $history): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Update->buildPermissionFor(ApplicationStageHistory::class));
    }

    public function delete(User $user, ApplicationStageHistory $history): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Delete->buildPermissionFor(ApplicationStageHistory::class));
    }
}
