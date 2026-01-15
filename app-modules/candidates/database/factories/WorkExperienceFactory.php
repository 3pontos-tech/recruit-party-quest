<?php

declare(strict_types=1);

namespace He4rt\Candidates\Database\Factories;

use He4rt\Candidates\Models\Candidate;
use He4rt\Candidates\Models\WorkExperience;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;

/** @extends Factory<WorkExperience> */
class WorkExperienceFactory extends Factory
{
    protected $model = WorkExperience::class;

    public function definition(): array
    {
        return [
            'company_name' => fake()->name(),
            'description' => fake()->text(),
            'start_date' => Date::now(),
            'end_date' => Date::now(),
            'is_currently_working_here' => fake()->boolean(),
            'metadata' => fake()->words(),
            'created_at' => Date::now(),
            'updated_at' => Date::now(),

            'candidate_id' => Candidate::factory(),
        ];
    }
}
