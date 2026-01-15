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
            'willing_to_relocate' => fake()->boolean(),
            'experience_level' => fake()->word(),
            'contact_links' => fake()->words(),
            'self_identified_gender' => fake()->word(),
            'has_disability' => fake()->boolean(),
            'source' => fake()->word(),
            'created_at' => Date::now(),
            'updated_at' => Date::now(),

            'user_id' => User::factory(),
        ];
    }
}
