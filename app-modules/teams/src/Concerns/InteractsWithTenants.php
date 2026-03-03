<?php

declare(strict_types=1);

namespace He4rt\Teams\Concerns;

use Filament\Panel;
use He4rt\Permissions\Roles;
use He4rt\Teams\Team;
use He4rt\Teams\TeamMember;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

trait InteractsWithTenants
{
    /**
     * @return HasMany<Team, $this>
     */
    public function ownedTenants(): HasMany
    {
        return $this->hasMany(Team::class, 'owner_id');
    }

    /**
     * @return BelongsToMany<Team, $this, TeamMember>
     */
    public function teams(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                Team::class,
                'team_user',
                'user_id',
                'team_id'
            )->using(TeamMember::class)->withTimestamps();
    }

    /**
     * @return Collection<int,Team>
     */
    public function getTenants(Panel $panel): Collection
    {
        if ($this->hasAnyRole([Roles::SuperAdmin, Roles::Admin])) {
            return Team::all();
        }

        return $this->teams->merge($this->ownedTenants)->unique('id');
    }

    public function canAccessTenant(Model $tenant): bool
    {
        if ($this->hasAnyRole([Roles::SuperAdmin, Roles::Admin])) {
            return true;
        }

        if ($this->teams()->whereKey($tenant)->exists()) {
            return true;
        }

        return (bool) $this->ownedTenants()->whereKey($tenant)->exists();
    }
}
