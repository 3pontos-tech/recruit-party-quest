<?php

declare(strict_types=1);

namespace He4rt\Users\Database\Factories;

use He4rt\Users\Models\UserGitHubConnection;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<UserGitHubConnection> */
class UserGitHubConnectionFactory extends Factory
{
    protected $model = UserGitHubConnection::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'github_id' => (string) fake()->unique()->numberBetween(1000000, 9999999),
            'github_username' => fake()->unique()->userName(),
            'access_token' => fake()->sha256(),
        ];
    }
}
