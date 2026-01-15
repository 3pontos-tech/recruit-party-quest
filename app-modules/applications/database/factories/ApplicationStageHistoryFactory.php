<?php

declare(strict_types=1);

namespace He4rt\Applications\Database\Factories;

use He4rt\Applications\Models\Application;
use He4rt\Applications\Models\ApplicationStageHistory;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ApplicationStageHistory> */
class ApplicationStageHistoryFactory extends Factory
{
    protected $model = ApplicationStageHistory::class;

    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'from_stage_id' => Stage::factory(),
            'to_stage_id' => Stage::factory(),
            'moved_by' => User::factory(),
            'notes' => fake()->sentence(),
            'created_at' => now(),
        ];
    }
}
