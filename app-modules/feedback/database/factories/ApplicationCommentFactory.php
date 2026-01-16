<?php

declare(strict_types=1);

namespace He4rt\Feedback\Database\Factories;

use He4rt\Applications\Models\Application;
use He4rt\Feedback\Models\ApplicationComment;
use He4rt\Teams\Team;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ApplicationComment> */
class ApplicationCommentFactory extends Factory
{
    protected $model = ApplicationComment::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'application_id' => Application::factory(),
            'author_id' => User::factory(),
            'content' => fake()->paragraph(),
            'is_internal' => true,
        ];
    }

    public function internal(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_internal' => true,
        ]);
    }

    public function external(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_internal' => false,
        ]);
    }
}
