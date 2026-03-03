<?php

declare(strict_types=1);

namespace He4rt\Teams\Database\Factories;

use He4rt\Teams\Team;
use He4rt\Teams\TeamMember;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TeamMember> */
class TeamMemberFactory extends Factory
{
    protected $model = TeamMember::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'user_id' => User::factory(),
        ];
    }
}
