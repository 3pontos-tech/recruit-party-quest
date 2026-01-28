<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Database\Factories;

use He4rt\Recruitment\Requisitions\Enums\EmploymentTypeEnum;
use He4rt\Recruitment\Requisitions\Enums\ExperienceLevelEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionPriorityEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkArrangementEnum;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Teams\Department;
use He4rt\Teams\Team;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

/** @extends Factory<JobRequisition> */
class JobRequisitionFactory extends Factory
{
    protected $model = JobRequisition::class;

    public function definition(): array
    {
        return [
            'slug' => (string) Str::uuid(),
            'work_arrangement' => fake()->randomElement(WorkArrangementEnum::cases()),
            'employment_type' => fake()->randomElement(EmploymentTypeEnum::cases()),
            'experience_level' => fake()->randomElement(ExperienceLevelEnum::cases()),
            'positions_available' => (string) fake()->numberBetween(1, 10),
            'salary_range_min' => fake()->numberBetween(3000, 5000),
            'salary_range_max' => fake()->numberBetween(6000, 10000),
            'salary_currency' => 'USD',
            'show_salary_to_candidates' => fake()->boolean(30),
            'status' => fake()->randomElement(RequisitionStatusEnum::cases()),
            'priority' => fake()->randomElement(RequisitionPriorityEnum::cases()),
            'target_start_at' => Date::now()->addMonths(1),
            'approved_at' => Date::now(),
            'published_at' => Date::now(),
            'closed_at' => null,
            'is_internal_only' => fake()->boolean(),
            'is_confidential' => fake()->boolean(),
            'created_at' => Date::now(),
            'updated_at' => Date::now(),

            'team_id' => Team::factory(),
            'department_id' => Department::factory(),
            'recruiter_id' => fn (array $attributes) => Recruiter::factory()->create(['team_id' => $attributes['team_id']])->getKey(),
            'created_by_id' => User::factory(),
        ];
    }
}
