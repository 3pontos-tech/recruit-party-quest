<?php

declare(strict_types=1);

namespace Database\Seeders;

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Candidates\Models\Education;
use He4rt\Candidates\Models\Skill;
use He4rt\Candidates\Models\WorkExperience;
use He4rt\Feedback\Models\ApplicationComment;
use He4rt\Feedback\Models\Evaluation;
use He4rt\Location\Address;
use He4rt\Recruitment\Requisitions\Enums\JobRequisitionItemTypeEnum;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Requisitions\Models\JobRequisitionItem;
use He4rt\Recruitment\Stages\Enums\StageTypeEnum;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Screening\Models\ScreeningQuestion;
use He4rt\Teams\Team;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

final class DevelopmentSeeder extends Seeder
{
    private const array ITEM_TAGS = [
        'description' => ['overview', 'company-culture', 'mission', 'vision'],
        'responsibility' => ['technical', 'leadership', 'collaboration', 'problem-solving'],
        'required_qualification' => ['must-have', 'essential', 'core-skill', 'experience'],
        'preferred_qualification' => ['nice-to-have', 'bonus', 'advanced', 'growth'],
        'benefit' => ['compensation', 'wellness', 'flexibility', 'development'],
    ];

    private User $adminUser;

    private Team $adminTeam;

    private Collection $departments;

    private Collection $skills;

    public function run(array $adminData = []): void
    {
        $this->command->info('Running Development Seeder...');

        if ($adminData === []) {
            $this->command->error('Admin data not provided to DevelopmentSeeder');

            return;
        }

        $this->adminUser = $adminData['admin'];
        $this->adminTeam = $adminData['team'];
        $this->departments = $adminData['departments'];

        $this->skills = $this->createFocusedSkills();

        $candidates = $this->createCandidates(10);

        $this->attachSkillsToCandidates($candidates);

        $requisitions = $this->createJobRequisitions();

        $this->createSmartApplications($candidates, $requisitions);

        $this->command->info('Development Seeder completed.');
    }

    private function createFocusedSkills(): Collection
    {
        $this->command->info('Creating focused skills...');

        $skills = collect();

        $hardSkills = [
            'PHP', 'Laravel', 'Symfony', 'Node.js', 'Python', 'Java', 'C#', '.NET',
            'JavaScript', 'TypeScript', 'React', 'Vue.js', 'Angular', 'HTML', 'CSS', 'Tailwind',
            'Flutter', 'React Native', 'Swift', 'Kotlin', 'Dart',
            'Docker', 'AWS', 'Azure', 'Kubernetes', 'CI/CD', 'Jenkins', 'GitLab',
            'MySQL', 'PostgreSQL', 'MongoDB', 'Redis', 'ElasticSearch',
            'Git', 'REST API', 'GraphQL',
        ];

        $softSkills = [
            'Leadership', 'Communication', 'Teamwork', 'Problem Solving',
            'Critical Thinking', 'Time Management', 'Adaptability',
            'Project Management', 'Agile', 'Scrum',
        ];

        $languages = [
            'English', 'Portuguese', 'Spanish', 'French', 'German',
        ];

        foreach ($hardSkills as $skillName) {
            $skill = Skill::factory()->create([
                'name' => $skillName,
                'category' => 'hard_skill',
            ]);

            $skills->push($skill);
        }

        foreach ($softSkills as $skillName) {
            $skill = Skill::factory()->create([
                'name' => $skillName,
                'category' => 'soft_skill',
            ]);

            $skills->push($skill);
        }

        foreach ($languages as $skillName) {
            $skill = Skill::factory()->create([
                'name' => $skillName,
                'category' => 'language',
            ]);

            $skills->push($skill);
        }

        return $skills;
    }

    private function createCandidates(int $cont = 3): Collection
    {
        $this->command->info('Creating candidates...');

        return Candidate::factory()
            ->count($cont)
            ->afterCreating(function (Candidate $candidate): void {
                $candidate->address()->save(
                    Address::factory()->make()
                );
                Education::factory()
                    ->count(fake()->numberBetween(1, 2))
                    ->create([
                        'candidate_id' => $candidate->getKey(),
                    ]);

                WorkExperience::factory()
                    ->count(fake()->numberBetween(2, 4))
                    ->create([
                        'candidate_id' => $candidate->getKey(),
                    ]);
            })
            ->create();
    }

    private function attachSkillsToCandidates(Collection $candidates): void
    {
        $this->command->info('Attaching skills to candidates...');

        $skillsByCategory = $this->skills->groupBy('category');

        $candidates->each(function ($candidate) use ($skillsByCategory): void {
            $candidateSkills = collect();

            $hardSkills = $skillsByCategory->get('hard_skill', collect());
            $selectedHardSkills = $hardSkills->random(min(fake()->numberBetween(3, 5), $hardSkills->count()));
            $candidateSkills = $candidateSkills->merge($selectedHardSkills);

            $softSkills = $skillsByCategory->get('soft_skill', collect());
            $selectedSoftSkills = $softSkills->random(min(fake()->numberBetween(2, 3), $softSkills->count()));
            $candidateSkills = $candidateSkills->merge($selectedSoftSkills);

            $languages = $skillsByCategory->get('language', collect());
            $selectedLanguages = $languages->random(min(fake()->numberBetween(1, 2), $languages->count()));
            $candidateSkills = $candidateSkills->merge($selectedLanguages);

            $pivotData = $candidateSkills->mapWithKeys(fn ($skill) => [
                $skill->getKey() => [
                    'years_of_experience' => fake()->numberBetween(1, 8),
                    'proficiency_level' => fake()->randomElement([2, 3, 4]),
                ],
            ])->all();

            $candidate->skills()->attach($pivotData);
        });
    }

    private function createJobRequisitions(): Collection
    {
        $this->command->info('Creating job requisitions...');

        $jobTitles = [
            'Senior PHP Developer',
            'Frontend React Developer',
            'Full-Stack Laravel Developer',
            'DevOps Engineer',
            'Mobile Flutter Developer',
        ];

        $requisitions = collect();

        foreach ($jobTitles as $title) {
            $requisition = JobRequisition::factory()
                ->afterCreating(function (JobRequisition $requisition) use ($title): void {
                    JobPosting::factory()->create([
                        'job_requisition_id' => $requisition->getKey(),
                        'title' => $title,
                        'description' => sprintf('Join our team as a %s. Work on exciting projects with modern technologies.',
                            $title),
                    ]);
                })
                ->create([
                    'team_id' => $this->adminTeam->getKey(),
                    'department_id' => $this->departments->random()->getKey(),
                    'created_by_id' => $this->adminUser->getKey(),
                ]);

            $this->seedRequisitionItems($requisition);
            $this->createRequisitionStages($requisition);
            $this->createScreeningQuestions($requisition);

            $requisitions->push($requisition);
        }

        return $requisitions;
    }

    private function createRequisitionStages(JobRequisition $requisition): void
    {
        $stages = [
            ['type' => StageTypeEnum::New, 'name' => 'New Applications'],
            ['type' => StageTypeEnum::Screening, 'name' => 'Initial Screening'],
            ['type' => StageTypeEnum::Assessment, 'name' => 'Technical Assessment'],
            ['type' => StageTypeEnum::Interview, 'name' => 'Technical Interview'],
            ['type' => StageTypeEnum::Interview, 'name' => 'Cultural Interview'],
            ['type' => StageTypeEnum::Offer, 'name' => 'Job Offer'],
        ];

        foreach ($stages as $index => $stageData) {
            Stage::factory()
                ->create([
                    'job_requisition_id' => $requisition->getKey(),
                    'team_id' => $requisition->team_id,
                    'stage_type' => $stageData['type'],
                    'name' => $stageData['name'],
                    'display_order' => $index + 1,
                ]);
        }
    }

    private function createScreeningQuestions(JobRequisition $requisition): void
    {
        ScreeningQuestion::factory()
            ->count(fake()->numberBetween(2, 4))
            ->create([
                'team_id' => $requisition->team_id,
                'display_order' => fake()->randomDigit(),
                'screenable_type' => Relation::getMorphAlias(JobRequisition::class),
                'screenable_id' => $requisition->getKey(),
            ]);
    }

    private function createSmartApplications(Collection $candidates, Collection $requisitions): void
    {
        $this->command->info('Creating smart applications with skill matching...');

        foreach ($requisitions as $requisition) {
            $requiredSkills = $this->extractRequiredSkills($requisition);

            $compatibleCandidates = $candidates->filter(function ($candidate) use ($requiredSkills): bool {
                $candidateSkills = $candidate->skills->pluck('name')->toArray();
                $matchCount = count(array_intersect($candidateSkills, $requiredSkills));

                return $matchCount >= 2;
            });

            $applicants = $compatibleCandidates->shuffle()->take(fake()->numberBetween(3, 6));

            $this->createRealisticApplications($applicants, $requisition);
        }
    }

    private function extractRequiredSkills(JobRequisition $requisition): array
    {
        $jobTitle = $requisition->post->title;

        $skillMaps = [
            'PHP' => ['PHP', 'Laravel', 'MySQL', 'Git'],
            'React' => ['JavaScript', 'React', 'HTML', 'CSS'],
            'Laravel' => ['PHP', 'Laravel', 'MySQL', 'JavaScript'],
            'DevOps' => ['Docker', 'AWS', 'Kubernetes', 'CI/CD'],
            'Flutter' => ['Flutter', 'Dart', 'Mobile', 'Git'],
        ];

        foreach ($skillMaps as $keyword => $skills) {
            if (str_contains($jobTitle, $keyword)) {
                return $skills;
            }
        }

        return ['Git', 'Agile'];
    }

    private function createRealisticApplications(Collection $candidates, JobRequisition $requisition): void
    {
        $stages = $requisition->stages->sortBy('display_order');

        $candidates->each(function ($candidate, $index) use ($requisition, $stages): void {
            $weights = [
                0 => 0.4,  // New
                1 => 0.3,  // Screening
                2 => 0.15, // Assessment
                3 => 0.1,  // Interview 1
                4 => 0.04, // Interview 2
                5 => 0.01, // Offer
            ];

            $currentStageIndex = $this->selectStageByWeight($weights);
            $currentStage = $stages->values()[$currentStageIndex] ?? $stages->first();
            $status = $this->mapStageToStatus($currentStage);

            $application = Application::factory()->create([
                'candidate_id' => $candidate->getKey(),
                'requisition_id' => $requisition->getKey(),
                'current_stage_id' => $currentStage->getKey(),
                'team_id' => $requisition->team_id,
                'status' => $status,
                'created_at' => fake()->dateTimeBetween('-2 months'),
            ]);

            if ($currentStageIndex > 0) {
                $this->addApplicationFeedback($application);
            }
        });
    }

    private function selectStageByWeight(array $weights): int
    {
        $random = fake()->randomFloat(2, 0, 1);
        $cumulative = 0;

        foreach ($weights as $index => $weight) {
            $cumulative += $weight;
            if ($random <= $cumulative) {
                return $index;
            }
        }

        return 0; // Fallback to first stage
    }

    private function mapStageToStatus(Stage $stage): ApplicationStatusEnum
    {
        return match ($stage->stage_type) {
            StageTypeEnum::Screening, StageTypeEnum::Assessment => ApplicationStatusEnum::InReview,
            StageTypeEnum::Interview => ApplicationStatusEnum::InProgress,
            StageTypeEnum::Offer => ApplicationStatusEnum::OfferExtended,
            default => ApplicationStatusEnum::New,
        };
    }

    private function addApplicationFeedback(Application $application): void
    {
        ApplicationComment::factory()
            ->count(fake()->numberBetween(1, 3))
            ->create([
                'application_id' => $application->getKey(),
                'team_id' => $application->team_id,
                'author_id' => $this->adminUser->getKey(),
            ]);

        if ($application->status === ApplicationStatusEnum::InProgress) {
            Evaluation::factory()
                ->count(fake()->numberBetween(1, 2))
                ->create([
                    'application_id' => $application->getKey(),
                    'team_id' => $application->team_id,
                    'stage_id' => $application->current_stage_id,
                    'evaluator_id' => $this->adminUser->getKey(),
                ]);
        }
    }

    private function seedRequisitionItems(JobRequisition $requisition): void
    {
        $itemsConfig = [
            ['type' => JobRequisitionItemTypeEnum::Responsibility, 'count' => fake()->numberBetween(4, 6)],
            ['type' => JobRequisitionItemTypeEnum::RequiredQualification, 'count' => fake()->numberBetween(4, 6)],
            ['type' => JobRequisitionItemTypeEnum::PreferredQualification, 'count' => fake()->numberBetween(2, 4)],
            ['type' => JobRequisitionItemTypeEnum::Benefit, 'count' => fake()->numberBetween(4, 6)],
        ];

        foreach ($itemsConfig as $config) {
            for ($i = 0; $i < $config['count']; $i++) {
                $item = JobRequisitionItem::factory()
                    ->ofType($config['type'])
                    ->recycle($requisition)
                    ->create(['order' => $i]);

                $this->attachTagsToItem($item, $config['type']);
            }
        }
    }

    private function attachTagsToItem(JobRequisitionItem $item, JobRequisitionItemTypeEnum $type): void
    {
        $availableTags = self::ITEM_TAGS[$type->value];
        $selectedTags = fake()->randomElements($availableTags, fake()->numberBetween(2, 3));

        $item->attachTags($selectedTags);
    }
}
