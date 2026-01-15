<?php

declare(strict_types=1);

namespace He4rt\Candidates\Database\Factories;

use He4rt\Candidates\Models\Candidate;
use He4rt\Candidates\Models\Education;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;

/** @extends Factory<Education> */
class EducationFactory extends Factory
{
    protected $model = Education::class;

    public function definition(): array
    {
        return [
            'institution' => fake()->word(),
            'degree' => fake()->word(),
            'field_of_study' => fake()->word(),
            'start_date' => Date::now(),
            'end_date' => Date::now(),
            'is_enrolled' => fake()->boolean(),
            'created_at' => Date::now(),
            'updated_at' => Date::now(),

            'candidate_id' => Candidate::factory(),
        ];
    }
}
