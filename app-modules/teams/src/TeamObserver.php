<?php

declare(strict_types=1);

namespace He4rt\Teams;

use He4rt\Permissions\Roles;
use He4rt\Users\User;

class TeamObserver
{
    public function created(Team $team): void
    {
        if ($team->owner_id) {
            User::query()->find($team->owner_id)?->assignRole(Roles::Owner->value);
        }
    }

    public function updated(Team $team): void
    {
        if (! $team->wasChanged('owner_id')) {
            return;
        }

        $oldOwnerId = $team->getOriginal('owner_id');
        $newOwnerId = $team->owner_id;

        if ($oldOwnerId && $oldOwnerId !== $newOwnerId) {
            $oldOwner = User::query()->find($oldOwnerId);
            if ($oldOwner && Team::query()->where('owner_id', $oldOwnerId)->doesntExist()) {
                $oldOwner->removeRole(Roles::Owner->value);
            }
        }

        if ($newOwnerId) {
            User::query()->find($newOwnerId)?->assignRole(Roles::Owner->value);
        }
    }

    public function deleted(Team $team): void
    {
        if ($team->owner_id && Team::query()->where('owner_id', $team->owner_id)->doesntExist()) {
            User::query()->find($team->owner_id)?->removeRole(Roles::Owner->value);
        }
    }

    public function restored(Team $team): void
    {
        if ($team->owner_id) {
            User::query()->find($team->owner_id)?->assignRole(Roles::Owner->value);
        }
    }
}
