<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Database\Factories;

use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Recruitment\Stages\Models\InterviewerPivot;
use He4rt\Recruitment\Stages\Models\Stage;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<InterviewerPivot> */
class InterviewerPivotFactory extends Factory
{
    protected $model = InterviewerPivot::class;

    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'pipeline_stage_id' => Stage::factory(),
            'recruiter_id' => Recruiter::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
