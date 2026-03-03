<?php

declare(strict_types=1);

namespace He4rt\Teams;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[ObservedBy(TeamMemberObserver::class)]
class TeamMember extends Pivot
{
    use HasFactory;
    protected $table = 'team_user';
}
