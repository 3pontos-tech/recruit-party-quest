<?php

declare(strict_types=1);

namespace He4rt\Applications\Database\Factories;

use He4rt\Applications\Models\Application;
use He4rt\Applications\Models\ApplicationView;
use He4rt\Teams\Team;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApplicationView>
 */
class ApplicationViewFactory extends Factory
{
    protected $model = ApplicationView::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'application_id' => Application::factory(),
            'viewed_by' => User::factory(),
            'viewed_at' => now(),
        ];
    }

    public function forApplication(Application $application): static
    {
        return $this->state(fn (): array => [
            'application_id' => $application->getKey(),
            'team_id' => $application->team_id,
        ]);
    }
}
