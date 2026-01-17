<?php

declare(strict_types=1);

namespace He4rt\Users;

use He4rt\Candidates\Models\Candidate;

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
