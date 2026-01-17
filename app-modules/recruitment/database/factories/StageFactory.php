<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Database\Factories;

use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Stages\Enums\StageTypeEnum;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Teams\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;

/** @extends Factory<Stage> */
class StageFactory extends Factory
{
    protected $model = Stage::class;

    public function definition(): array
    {
        $stageData = [
            'Initial Screening' => 'Reviewing application and basic qualifications.',
            'Technical Interview' => 'Deep dive into technical skills and problem-solving abilities.',
            'Cultural Fit' => 'Assessing alignment with company values and team dynamics.',
            'Final Interview' => 'Conversation with hiring manager or leadership team.',
            'Technical Challenge' => 'Practical assessment of coding or design skills.',
            'Offer Stage' => 'Finalizing terms and conditions of employment.',
        ];

        $name = fake()->randomElement(array_keys($stageData));

        return [
            'name' => $name,
            'stage_type' => fake()->randomElement(StageTypeEnum::cases()),
            'display_order' => fake()->numberBetween(1, 10),
            'description' => $stageData[$name],
            'expected_duration_days' => fake()->numberBetween(1, 14),
            'active' => true,
            'created_at' => Date::now(),
            'updated_at' => Date::now(),

            'job_requisition_id' => JobRequisition::factory(),
            'team_id' => Team::factory(),
        ];
    }
}
