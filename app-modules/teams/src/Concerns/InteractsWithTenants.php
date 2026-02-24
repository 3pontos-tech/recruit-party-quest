<?php

declare(strict_types=1);

namespace He4rt\Teams\Concerns;

use Filament\Panel;
use He4rt\Teams\Team;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
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
     * @return BelongsToMany<Team, $this, Pivot>
     */
    public function teams(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                Team::class,
                'team_user',
                'user_id',
                'team_id'
            )->withTimestamps();
    }

    /**
     * @return Collection<int,Team>
     */
    public function getTenants(Panel $panel): Collection
    {
        return $this->teams;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->teams()->whereKey($tenant)->exists();
    }
}
