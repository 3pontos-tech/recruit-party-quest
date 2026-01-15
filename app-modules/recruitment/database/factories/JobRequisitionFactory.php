<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Database\Factories;

use He4rt\Recruitment\Recruitment\JobRequisition;
use He4rt\Teams\Department;
use He4rt\Teams\Team;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;

/** @extends Factory<JobRequisition> */
class JobRequisitionFactory extends Factory
{
    protected $model = JobRequisition::class;

    public function definition(): array
    {
        return [
            'work_arrangement' => fake()->word(),
            'employment_type' => fake()->word(),
            'experience_level' => fake()->word(),
            'positions_available' => fake()->word(),
            'salary_range_min' => fake()->randomNumber(),
            'salary_range_max' => fake()->randomNumber(),
            'salary_currency' => fake()->word(),
            'status' => fake()->word(),
            'priority' => fake()->word(),
            'target_start_at' => Date::now(),
            'approved_at' => Date::now(),
            'published_at' => Date::now(),
            'closed_at' => Date::now(),
            'is_internal_only' => fake()->boolean(),
            'is_confidential' => fake()->boolean(),
            'created_at' => Date::now(),
            'updated_at' => Date::now(),

            'team_id' => Team::factory(),
            'department_id' => Department::factory(),
            'hiring_manager_id' => User::factory(),
            'created_by_id' => User::factory(),
        ];
    }
}
