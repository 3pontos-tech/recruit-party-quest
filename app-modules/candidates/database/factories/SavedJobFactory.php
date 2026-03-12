<?php

declare(strict_types=1);

namespace He4rt\Candidates\Database\Factories;

use He4rt\Candidates\Models\Candidate;
use He4rt\Candidates\Models\SavedJob;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SavedJob> */
class SavedJobFactory extends Factory
{
    protected $model = SavedJob::class;

    public function definition(): array
    {
        return [
            'candidate_id' => Candidate::factory(),
            'job_requisition_id' => JobRequisition::factory(),
            'saved_at' => now(),
        ];
    }
}
