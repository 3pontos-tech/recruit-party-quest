<?php

declare(strict_types=1);

namespace Database\Seeders;

use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Candidates\Models\Education;
use He4rt\Candidates\Models\Skill;
use He4rt\Candidates\Models\WorkExperience;
use He4rt\Feedback\Models\ApplicationComment;
use He4rt\Feedback\Models\Evaluation;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Stages\Enums\StageTypeEnum;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Screening\Models\ScreeningQuestion;
use He4rt\Teams\Department;
use He4rt\Teams\Team;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

final class DevelopmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Running Development Seeder...');

        $teams = $this->seedTeamsAndDepartments();
        $departments = Department::all();
        $users = $this->seedUsers($teams);
        $candidates = $this->seedCandidates($users);
        $this->seedCandidateDetails($candidates);

        $requisitions = $this->seedJobRequisitions($teams, $departments, $users);
        $this->seedJobPostings($requisitions);
        $this->seedScreeningQuestions($requisitions);

        $applications = $this->seedApplications($requisitions, $candidates, $users);
        $this->seedApplicationInteractions($applications, $users);

        $this->command->info('Development Seeder completed.');
    }

    private function seedTeamsAndDepartments(): Collection
    {
        $this->command->warn('Seeding teams and departments...');

        $teams = Team::factory()
            ->count(3)
            ->has(Department::factory()->count(2))
            ->create();

        $this->command->info('Teams and departments seeded.');

        return $teams;
    }

    private function seedUsers(Collection $teams): Collection
    {
        $this->command->warn('Seeding users and assigning to teams...');

        $users = User::factory()
            ->count(20)
            ->create();

        $users->each(function (User $user) use ($teams): void {
            $user->teams()->attach($teams->random());
        });

        $this->command->info('Users seeded.');

        return $users;
    }

    private function seedCandidates(Collection $users): Collection
    {
        $this->command->warn('Seeding candidates from existing users...');

        $candidates = Candidate::factory()
            ->count(15)
            ->recycle($users)
            ->create();

        $this->command->info('Candidates seeded.');

        return $candidates;
    }

    private function seedCandidateDetails(Collection $candidates): void
    {
        $this->command->warn('Seeding candidate details (skills, education, work experience)...');

        $skills = Skill::factory()->count(10)->create();

        $candidates->each(function (Candidate $candidate) use ($skills): void {
            Education::factory()->count(fake()->numberBetween(1, 2))->recycle($candidate)->create();
            WorkExperience::factory()->count(fake()->numberBetween(1, 3))->recycle($candidate)->create();

            $candidate->skills()->attach(
                $skills->random(fake()->numberBetween(2, 5))->pluck('id')->toArray(),
                [
                    'years_of_experience' => fake()->numberBetween(1, 10),
                    'proficiency_level' => fake()->randomElement(['beginner', 'intermediate', 'advanced', 'expert']),
                ]
            );
        });

        $this->command->info('Candidate details seeded.');
    }

    private function seedJobRequisitions(
        Collection $teams,
        Collection $departments,
        Collection $users
    ): Collection {
        $this->command->warn('Seeding job requisitions...');

        $requisitions = JobRequisition::factory()
            ->count(5)
            ->recycle($teams)
            ->recycle($departments)
            ->recycle($users) // Will recycle for hiring_manager_id and created_by_id
            ->create();

        $this->command->info('Job requisitions seeded.');

        return $requisitions;
    }

    private function seedJobPostings(Collection $requisitions): void
    {
        $this->command->warn('Seeding job postings...');

        JobPosting::factory()
            ->count($requisitions->count())
            ->recycle($requisitions)
            ->create();

        $this->command->info('Job postings seeded.');
    }

    private function seedScreeningQuestions(Collection $requisitions): void
    {
        $this->command->warn('Seeding screening questions...');

        $requisitions->each(function (JobRequisition $requisition, int $index): void {
            ScreeningQuestion::factory()
                ->count(fake()->numberBetween(2, 4))
                ->recycle($requisition)
                ->create([
                    'requisition_id' => $requisition->id,
                    'display_order' => $index,
                ]);
        });

        $this->command->info('Screening questions seeded.');
    }

    private function seedApplications(
        Collection $requisitions,
        Collection $candidates,
        Collection $users
    ): Collection {
        $this->command->warn('Seeding applications...');

        // Create stages for requisitions if they don't have them
        $requisitions->each(function (JobRequisition $requisition): void {
            if ($requisition->stages()->count() === 0) {
                foreach (StageTypeEnum::cases() as $stageType) {
                    Stage::factory()->count(3)
                        ->recycle($requisition)
                        ->create([
                            'stage_type' => $stageType,
                            'name' => $stageType->getLabel(),
                        ]);
                }
            }

            $requisition->refresh();
        });

        $applications = collect();

        // Create pairs of (requisition, candidate) to avoid duplicates
        $pairs = [];
        foreach ($requisitions as $requisition) {
            foreach ($candidates as $candidate) {
                $pairs[] = ['requisition_id' => $requisition->id, 'candidate_id' => $candidate->id];
            }
        }

        // Shuffle and take a subset
        $selectedPairs = collect($pairs)->shuffle()->take(20);

        foreach ($selectedPairs as $pair) {
            $requisition = $requisitions->firstWhere('id', $pair['requisition_id']);
            $candidate = $candidates->firstWhere('id', $pair['candidate_id']);
            $requisitionStages = $requisition->stages;

            $applications->push(
                Application::factory()
                    ->recycle($requisition)
                    ->recycle($candidate)
                    ->recycle($users)
                    ->state([
                        'current_stage_id' => $requisitionStages->count() > 0 ? $requisitionStages->random()->id : null,
                    ])
                    ->create()
            );
        }

        $this->command->info('Applications seeded.');

        return Collection::make($applications);
    }

    private function seedApplicationInteractions(Collection $applications, Collection $users): void
    {
        $this->command->warn('Seeding application interactions (comments, evaluations)...');

        $applications->each(function (Application $application) use ($users): void {
            ApplicationComment::factory()
                ->count(fake()->numberBetween(0, 3))
                ->recycle($application)
                ->recycle($users)
                ->create();

            if ($application->current_stage_id) {
                Evaluation::factory()
                    ->count(fake()->numberBetween(0, 2))
                    ->recycle($application)
                    ->state(['stage_id' => $application->current_stage_id])
                    ->recycle($users)
                    ->create();
            }
        });

        $this->command->info('Application interactions seeded.');
    }
}
