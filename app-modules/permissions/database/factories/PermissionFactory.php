<?php

declare(strict_types=1);

namespace He4rt\Permissions\Database\Factories;

use He4rt\Permissions\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;

/** @extends Factory<Permission> */
class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    public function definition(): array
    {
        return [
            'guard_name' => fake()->name(),
            'name' => fake()->name(),
            'resource' => fake()->word(),
            'resource_group' => fake()->word(),
            'created_at' => Date::now(),
            'updated_at' => Date::now(),
        ];
    }
}
