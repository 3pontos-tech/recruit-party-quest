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
            'name' => fake()->company(),
            'description' => fake()->sentence(10),
            'slug' => fake()->unique()->slug(),
            'status' => TeamStatus::Active->value,
            'contact_email' => fake()->unique()->companyEmail(),
            'about' => fake()->paragraphs(3, true),
            'work_schedule' => 'Monday to Friday, 9am - 6pm. Flexible hours available.',
            'accessibility_accommodations' => fake()->sentence(),
            'is_disability_confident' => fake()->boolean(),

            'owner_id' => User::factory(),
        ];
    }
}
