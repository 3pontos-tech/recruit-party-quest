<?php

declare(strict_types=1);

namespace He4rt\Location;

use He4rt\Permissions\PermissionsEnum;
use He4rt\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AddressPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::ViewAny->buildPermissionFor(Address::class));
    }

    public function view(User $user, Address $address): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::View->buildPermissionFor(Address::class));
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Create->buildPermissionFor(Address::class));
    }

    public function update(User $user, Address $address): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Update->buildPermissionFor(Address::class));
    }

    public function delete(User $user, Address $address): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Delete->buildPermissionFor(Address::class));
    }

    public function restore(User $user, Address $address): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::Restore->buildPermissionFor(Address::class));
    }

    public function forceDelete(User $user, Address $address): bool
    {
        return $user->hasPermissionTo(PermissionsEnum::ForceDelete->buildPermissionFor(Address::class));
    }
}
