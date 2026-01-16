<?php

declare(strict_types=1);

namespace He4rt\Teams\Concerns;

use He4rt\Teams\Team;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait for models that belong to a Team (tenant-aware).
 *
 * @property string $team_id
 * @property-read Team $team
 */
trait BelongsToTeam
{
    /**
     * @return BelongsTo<Team, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Scope a query to only include records for a specific team.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    protected function scopeForTeam(Builder $query, Team|string $team): Builder
    {
        $teamId = $team instanceof Team ? $team->id : $team;

        return $query->where($this->getTable().'.team_id', $teamId);
    }

    /**
     * Scope a query to only include records for the current tenant.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    protected function scopeForCurrentTeam(Builder $query): Builder
    {
        $currentTeam = filament()->getTenant();

        if ($currentTeam instanceof Team) {
            return $query->where($this->getTable().'.team_id', $currentTeam->id);
        }

        return $query;
    }
}
