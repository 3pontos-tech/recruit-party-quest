<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Database\Factories;

use He4rt\Recruitment\Requisitions\Enums\EmploymentTypeEnum;
use He4rt\Recruitment\Requisitions\Enums\ExperienceLevelEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionPriorityEnum;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkArrangementEnum;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Staff\Recruiter\Recruiter;
use He4rt\Teams\Department;
use He4rt\Teams\Team;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

/** @extends Factory<JobRequisition> */
class JobRequisitionFactory extends Factory
{
    protected $model = JobRequisition::class;

    public function definition(): array
    {
        $experienceLevel = fake()->randomElement(ExperienceLevelEnum::cases());
        $salaryRange = $this->getSalaryRangeByExperience($experienceLevel);

        $createdDaysAgo = fake()->numberBetween(5, 60);
        $approvedDaysAgo = fake()->numberBetween(1, min($createdDaysAgo - 1, 30));
        $publishedDaysAgo = fake()->numberBetween(0, $approvedDaysAgo);

        $createdAt = Date::now()->subDays($createdDaysAgo);
        $approvedAt = Date::now()->subDays($approvedDaysAgo);
        $publishedAt = $publishedDaysAgo > 0 ? Date::now()->subDays($publishedDaysAgo) : null;

        return [
            'slug' => (string) Str::uuid(),
            'work_arrangement' => $this->getRealisticWorkArrangement(),
            'employment_type' => $this->getRealisticEmploymentType(),
            'experience_level' => $experienceLevel,
            'positions_available' => (string) $this->getRealisticPositionsCount(),
            'salary_range_min' => $salaryRange['min'],
            'salary_range_max' => $salaryRange['max'],
            'salary_currency' => 'USD',
            'show_salary_to_candidates' => fake()->boolean(60),
            'status' => fake()->randomElement(RequisitionStatusEnum::cases()),
            'priority' => $this->getRealisticPriority(),
            'target_start_at' => Date::now()->addWeeks(fake()->numberBetween(2, 8)),
            'approved_at' => $approvedAt,
            'published_at' => $publishedAt,
            'closed_at' => null,
            'is_internal_only' => fake()->boolean(15),
            'is_confidential' => fake()->boolean(5),
            'created_at' => $createdAt,
            'updated_at' => fake()->dateTimeBetween($createdAt),

            'team_id' => Team::factory(),
            'department_id' => Department::factory(),
            'recruiter_id' => fn (array $attributes) => Recruiter::factory()->create(['team_id' => $attributes['team_id']])->getKey(),
            'created_by_id' => User::factory(),
        ];
    }

    private function getSalaryRangeByExperience($experienceLevel): array
    {
        $ranges = match ($experienceLevel->value ?? $experienceLevel) {
            'intern' => ['min' => fake()->numberBetween(2000, 3000), 'max' => fake()->numberBetween(3500, 4500)],
            'entry_level' => ['min' => fake()->numberBetween(4000, 5000), 'max' => fake()->numberBetween(6000, 7500)],
            'mid_level' => ['min' => fake()->numberBetween(6000, 8000), 'max' => fake()->numberBetween(9000, 12000)],
            'senior' => ['min' => fake()->numberBetween(8000, 12000), 'max' => fake()->numberBetween(13000, 18000)],
            'lead' => ['min' => fake()->numberBetween(12000, 15000), 'max' => fake()->numberBetween(18000, 25000)],
            'principal' => ['min' => fake()->numberBetween(15000, 20000), 'max' => fake()->numberBetween(22000, 30000)],
            default => ['min' => fake()->numberBetween(5000, 7000), 'max' => fake()->numberBetween(8000, 12000)],
        };

        if ($ranges['max'] <= $ranges['min']) {
            $ranges['max'] = $ranges['min'] + fake()->numberBetween(2000, 5000);
        }

        return $ranges;
    }

    private function getRealisticWorkArrangement()
    {
        $distribution = [
            70 => WorkArrangementEnum::Remote,
            20 => WorkArrangementEnum::Hybrid,
            10 => WorkArrangementEnum::OnSite,
        ];

        $rand = fake()->numberBetween(1, 100);
        $cumulative = 0;

        foreach ($distribution as $percentage => $arrangement) {
            $cumulative += $percentage;
            if ($rand <= $cumulative) {
                return $arrangement;
            }
        }

        return fake()->randomElement(WorkArrangementEnum::cases());
    }

    private function getRealisticEmploymentType()
    {
        return fake()->boolean(85)
            ? EmploymentTypeEnum::FullTimeEmployee
            : fake()->randomElement(EmploymentTypeEnum::cases());
    }

    private function getRealisticPositionsCount(): int
    {
        $distribution = [
            60 => 1,
            25 => 2,
            10 => 3,
            4 => fake()->numberBetween(4, 6),
            1 => fake()->numberBetween(7, 15),
        ];

        $rand = fake()->numberBetween(1, 100);
        $cumulative = 0;

        foreach ($distribution as $percentage => $count) {
            $cumulative += $percentage;
            if ($rand <= $cumulative) {
                return $count;
            }
        }

        return 1;
    }

    private function getRealisticPriority()
    {
        $distribution = [
            70 => RequisitionPriorityEnum::High,
            40 => RequisitionPriorityEnum::Medium,
            15 => RequisitionPriorityEnum::Low,
        ];

        $rand = fake()->numberBetween(1, 100);
        $cumulative = 0;

        foreach ($distribution as $percentage => $priority) {
            $cumulative += $percentage;
            if ($rand <= $cumulative) {
                return $priority;
            }
        }

        return fake()->randomElement(RequisitionPriorityEnum::cases());
    }
}
