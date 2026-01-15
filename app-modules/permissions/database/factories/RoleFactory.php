<?php

declare(strict_types=1);

namespace He4rt\Permissions\Database\Factories;

use He4rt\Permissions\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;

/** @extends Factory<Role> */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'created_at' => Date::now(),
            'updated_at' => Date::now(),
            'guard_name' => fake()->name(),
            'name' => fake()->name(),
        ];
    }
}
