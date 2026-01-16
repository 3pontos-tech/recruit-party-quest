<?php

declare(strict_types=1);

namespace He4rt\Candidates\Database\Factories;

use He4rt\Candidates\Models\Candidate;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;

/** @extends Factory<Candidate> */
class CandidateFactory extends Factory
{
    protected $model = Candidate::class;

    public function definition(): array
    {
        return [
            'phone_number' => fake()->phoneNumber(),
            'headline' => fake()->jobTitle(),
            'summary' => fake()->paragraph(),
            'availability_date' => fake()->dateTimeBetween('now', '+6 months')->format('Y-m-d'),
            'willing_to_relocate' => fake()->boolean(),
            'is_open_to_remote' => fake()->boolean(),
            'expected_salary' => fake()->randomFloat(2, 30000, 150000),
            'expected_salary_currency' => 'USD',
            'linkedin_url' => fake()->url(),
            'portfolio_url' => fake()->url(),
            'experience_level' => fake()->randomElement(['junior', 'mid', 'senior', 'lead']),
            'contact_links' => [
                'email' => fake()->safeEmail(),
                'twitter' => 'https://twitter.com/'.fake()->userName(),
            ],
            'self_identified_gender' => fake()->randomElement(['male', 'female', 'non-binary', 'prefer not to say']),
            'has_disability' => fake()->boolean(),
            'source' => fake()->randomElement(['linkedin', 'referral', 'website']),
            'created_at' => Date::now(),
            'updated_at' => Date::now(),

            'user_id' => User::factory(),
        ];
    }
}
