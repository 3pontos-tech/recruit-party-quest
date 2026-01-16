<?php

declare(strict_types=1);

namespace He4rt\Teams\Database\Factories;

use He4rt\Teams\Department;
use He4rt\Teams\Team;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;

/** @extends Factory<Department> */
class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['IT', 'HR', 'Finance']),
            'description' => fake()->text(),
            'created_at' => Date::now(),
            'updated_at' => Date::now(),

            'team_id' => Team::factory(),
            'head_user_id' => User::factory(),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Department $department): void {
            if ($department->head_user_id && $department->team_id) {
                $department->team->members()->syncWithoutDetaching([$department->head_user_id]);
            }
        });
    }
}
