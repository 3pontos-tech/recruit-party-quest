<?php

declare(strict_types=1);

namespace He4rt\Users\Database\Factories;

use He4rt\Users\Enums\OAuthProvider;
use He4rt\Users\Models\SocialAccount;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SocialAccount> */
class SocialAccountFactory extends Factory
{
    protected $model = SocialAccount::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'provider' => OAuthProvider::GitHub,
            'provider_id' => (string) fake()->unique()->numberBetween(1000000, 9999999),
            'provider_username' => fake()->unique()->userName(),
            'access_token' => fake()->sha256(),
        ];
    }

    public function github(): static
    {
        return $this->state(['provider' => OAuthProvider::GitHub]);
    }

    public function google(): static
    {
        return $this->state(['provider' => OAuthProvider::Google]);
    }

    public function linkedin(): static
    {
        return $this->state(['provider' => OAuthProvider::LinkedIn]);
    }
}
