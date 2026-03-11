<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Requisitions\Observers;

use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Stages\Enums\StageTypeEnum;

final class JobRequisitionObserver
{
    public function created(JobRequisition $jobRequisition): void
    {
        $stagesConfig = [
            [
                'type' => StageTypeEnum::New,
                'name' => 'New Applications',
                'description' => 'Initial application submission and registration',
                'duration' => 1,
            ],
            [
                'type' => StageTypeEnum::Screening,
                'name' => 'Resume Screening',
                'description' => 'Review of resume and basic qualifications',
                'duration' => 3,
            ],
            [
                'type' => StageTypeEnum::Assessment,
                'name' => 'Technical Assessment',
                'description' => 'Technical skills evaluation and coding challenge',
                'duration' => 7,
            ],
            [
                'type' => StageTypeEnum::Interview,
                'name' => 'Technical Interview',
                'description' => 'In-depth technical discussion with the team',
                'duration' => 5,
            ],
            [
                'type' => StageTypeEnum::Offer,
                'name' => 'Offer',
                'description' => 'Job offer preparation and negotiation',
                'duration' => 5,
            ],
            [
                'type' => StageTypeEnum::Hired,
                'name' => 'Hired',
                'description' => 'Candidate accepted the offer',
                'duration' => 1,
            ],
            [
                'type' => StageTypeEnum::Declined,
                'name' => 'Declined',
                'description' => 'Candidate declined the offer',
                'duration' => 1,
            ],
            [
                'type' => StageTypeEnum::Rejected,
                'name' => 'Rejected',
                'description' => 'Candidates not selected for the position',
                'duration' => 1,
            ],
        ];

        foreach ($stagesConfig as $index => $config) {
            $jobRequisition->stages()->create([
                'team_id' => $jobRequisition->team_id,
                'stage_type' => $config['type'],
                'name' => $config['name'],
                'description' => $config['description'],
                'expected_duration_days' => $config['duration'],
                'display_order' => $index + 1,
                'active' => true,
                'hidden' => false,
            ]);
        }
    }

    public function deleted(JobRequisition $jobRequisition): void
    {
        $jobRequisition->applications()->delete();
    }

    public function restored(JobRequisition $jobRequisition): void
    {
        $jobRequisition->applications()->withTrashed()->restore();
    }
}
