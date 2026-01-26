<?php

declare(strict_types=1);

namespace Database\Seeders;

use Exception;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Candidates\Models\Education;
use He4rt\Candidates\Models\Skill;
use He4rt\Candidates\Models\WorkExperience;
use He4rt\Feedback\Models\ApplicationComment;
use He4rt\Feedback\Models\Evaluation;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Stages\Enums\StageTypeEnum;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Screening\Models\ScreeningQuestion;
use He4rt\Teams\Department;
use He4rt\Teams\Team;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Seeder;

final class DevelopmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Running Development Seeder...');

        Team::factory()->count(3)
            ->has(Department::factory()->count(2))
            ->create();

        $teams = Team::all();

        $entitiesCount = 3;
        $users = User::factory()->count($entitiesCount)->create();

        try {
            $users->each(fn (User $user) => $user->teams()->attach($teams->random()));
        } catch (Exception) {

        }

        $candidates = Candidate::factory()->count($entitiesCount)->recycle($users)->create();

        $skills = Skill::factory()->count(10)->create();

        $candidates->each(function (Candidate $candidate) use ($skills): void {
            Education::factory()->count(fake()->numberBetween(1, 2))->recycle($candidate)->create();
            WorkExperience::factory()->count(fake()->numberBetween(1, 3))->recycle($candidate)->create();

            $candidate->skills()->attach(
                $skills->random(fake()->numberBetween(2, 5))->pluck('id')->toArray(),
                [
                    'years_of_experience' => fake()->numberBetween(1, 10),
                    'proficiency_level' => fake()->randomElement([1, 2, 3, 4]),
                ]
            );
        });

        $requisitions = JobRequisition::factory()
            ->count(5)
            ->hasPost()
            ->recycle($teams)
            ->recycle(Department::all())
            ->recycle($users)
            ->create();

        $requisitions->each(function (JobRequisition $requisition): void {
            ScreeningQuestion::factory()
                ->count(fake()->numberBetween(2, 4))
                ->recycle($requisition->team)
                ->create([
                    'display_order' => fake()->randomDigit(),
                    'screenable_type' => Relation::getMorphAlias(JobRequisition::class),
                    'screenable_id' => $requisition->id,
                ]);

            $stages = [
                ['type' => StageTypeEnum::New, 'name' => 'Newcomers'],
                ['type' => StageTypeEnum::Screening, 'name' => 'Initial Screening'],
                ['type' => StageTypeEnum::Assessment, 'name' => 'Technical Challenge'],
                ['type' => StageTypeEnum::Interview, 'name' => 'Technical Interview'],
                ['type' => StageTypeEnum::Interview, 'name' => 'Cultural Fit'],
                ['type' => StageTypeEnum::Offer, 'name' => 'Offer Stage'],
            ];

            foreach ($stages as $index => $stageData) {
                Stage::factory()
                    ->recycle($requisition)
                    ->recycle($requisition->team)
                    ->hasInterviewers(2)
                    ->create([
                        'stage_type' => $stageData['type'],
                        'name' => $stageData['name'],
                        'display_order' => $index + 1,
                    ]);

            }
        });

        collect();
        $pairs = [];
        foreach ($requisitions as $requisition) {
            foreach ($candidates as $candidate) {
                $pairs[] = ['requisition_id' => $requisition->id, 'candidate_id' => $candidate->id];
            }
        }

        $selectedPairs = collect($pairs)->shuffle()->all();

        foreach ($selectedPairs as $pair) {
            $requisition = $requisitions->firstWhere('id', $pair['requisition_id']);
            $candidate = $candidates->firstWhere('id', $pair['candidate_id']);
            $requisitionStages = $requisition->stages;

            $application = Application::factory()
                ->recycle($requisition)
                ->recycle($candidate)
                ->recycle($requisition->team)
                ->recycle($users)
                ->state([
                    'current_stage_id' => $requisitionStages->first()->getKey(),
                    'status' => ApplicationStatusEnum::New,
                ])
                ->create();

            ApplicationComment::factory()
                ->count(fake()->numberBetween(0, 3))
                ->recycle($application->team)
                ->recycle($application)
                ->recycle($users)
                ->create();

            Evaluation::factory()
                ->count(fake()->numberBetween(0, 2))
                ->recycle($application->team)
                ->recycle($application)
                ->state(['stage_id' => $application->current_stage_id])
                ->recycle($users)
                ->create();
        }

        $this->command->info('Development Seeder completed.');
    }
}
