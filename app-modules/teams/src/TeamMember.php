<?php

declare(strict_types=1);

namespace He4rt\Teams;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property string $team_id
 * @property string $user_id
 */
#[ObservedBy(TeamMemberObserver::class)]
class TeamMember extends Pivot
{
    use HasFactory;

    protected $table = 'team_user';
}
