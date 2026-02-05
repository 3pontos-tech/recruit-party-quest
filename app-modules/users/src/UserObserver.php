<?php

declare(strict_types=1);

namespace He4rt\Users;

use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Permissions\Permission;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        if (! $user->candidate) {
            Candidate::query()->create([
                'user_id' => $user->id,
                'is_onboarded' => false,
                'preferred_language' => 'en',
                'expected_salary_currency' => 'USD',
                'is_open_to_remote' => true,
            ]);
            $permission = Permission::query()->firstOrCreate([
                'name' => 'view_applications',
                'guard_name' => 'web',
                'resource' => Application::class,
                'resource_group' => 'Applications',
                'action' => 'view',
            ]);

            $user->givePermissionTo($permission->name);
        }
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
