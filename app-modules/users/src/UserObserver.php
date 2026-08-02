<?php

declare(strict_types=1);

namespace He4rt\Users;

use He4rt\Permissions\Roles;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $user->assignRole(Roles::User);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void {}

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void {}

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void {}

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void {}
}
