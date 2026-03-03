<?php

declare(strict_types=1);

namespace He4rt\Teams;

use He4rt\Recruitment\Staff\Recruiter\Recruiter;

class TeamMemberObserver
{
    public function created(TeamMember $teamMember): void
    {
        Recruiter::query()->firstOrCreate(
            ['user_id' => $teamMember->user_id, 'team_id' => $teamMember->team_id],
            ['is_active' => true]
        );
    }

    public function deleted(TeamMember $teamMember): void
    {
        Recruiter::query()
            ->where('user_id', $teamMember->user_id)
            ->where('team_id', $teamMember->team_id)
            ->delete();
    }
}
