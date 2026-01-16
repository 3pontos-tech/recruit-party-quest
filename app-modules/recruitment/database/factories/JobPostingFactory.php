<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Database\Factories;

use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Teams\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;

/** @extends Factory<JobPosting> */
class JobPostingFactory extends Factory
{
    protected $model = JobPosting::class;

    public function definition(): array
    {
        return [
            'title' => fake()->word(),
            'slug' => fake()->slug(),
            'summary' => fake()->text(),
            'description' => fake()->text(),
            'responsibilities' => fake()->words(),
            'required_qualifications' => fake()->words(),
            'preferred_qualifications' => fake()->words(),
            'benefits' => fake()->words(),
            'about_company' => fake()->company(),
            'about_team' => fake()->word(),
            'work_schedule' => fake()->word(),
            'accessibility_accommodations' => fake()->word(),
            'is_disability_confident' => fake()->boolean(),
            'external_post_url' => fake()->url(),
            'created_at' => Date::now(),
            'updated_at' => Date::now(),

            'job_requisition_id' => JobRequisition::factory(),
            'team_id' => Team::factory(),
        ];
    }
}
