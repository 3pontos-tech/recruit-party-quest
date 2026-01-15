<?php

declare(strict_types=1);

namespace He4rt\Teams\Database\Factories;

use He4rt\Teams\Team;
use He4rt\Teams\TeamStatus;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Team> */
class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'description' => fake()->text(),
            'slug' => fake()->slug(),
            'status' => TeamStatus::Active->value,
            'contact_email' => fake()->unique()->safeEmail(),

            'owner_id' => User::factory(),
        ];
    }
}
