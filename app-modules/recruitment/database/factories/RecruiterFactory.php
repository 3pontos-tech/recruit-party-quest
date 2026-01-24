<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Database\Factories;

use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Teams\Team;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;

/**
 * @extends Factory<Recruiter>
 */
class RecruiterFactory extends Factory
{
    protected $model = Recruiter::class;

    public function definition(): array
    {
        return [
            'is_active' => true,
            'max_active_candidates' => fake()->randomNumber(),
            'max_active_requisitions' => fake()->randomNumber(),
            'created_at' => Date::now(),
            'updated_at' => Date::now(),

            'user_id' => User::factory(),
            'team_id' => Team::factory(),
        ];
    }
}
