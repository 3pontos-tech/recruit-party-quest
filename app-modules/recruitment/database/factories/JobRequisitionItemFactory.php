<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Database\Factories;

use He4rt\Recruitment\Requisitions\Enums\JobRequisitionItemTypeEnum;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Requisitions\Models\JobRequisitionItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;

/** @extends Factory<JobRequisitionItem> */
class JobRequisitionItemFactory extends Factory
{
    protected $model = JobRequisitionItem::class;

    public function definition(): array
    {
        $type = fake()->randomElement(JobRequisitionItemTypeEnum::cases());

        return [
            'type' => $type,
            'content' => $this->generateContentForType($type),
            'order' => fake()->numberBetween(0, 10),
            'created_at' => Date::now(),
            'updated_at' => Date::now(),

            'job_requisition_id' => JobRequisition::factory(),
        ];
    }

    public function ofType(JobRequisitionItemTypeEnum $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
            'content' => $this->generateContentForType($type),
        ]);
    }

    public function withTags(array $tags): static
    {
        return $this->afterCreating(fn (JobRequisitionItem $item) => $item->attachTags($tags));
    }

    private function generateContentForType(JobRequisitionItemTypeEnum $type): string
    {
        return match ($type) {
            JobRequisitionItemTypeEnum::Responsibility => fake()->randomElement([
                'Design and implement scalable software solutions',
                'Collaborate with cross-functional teams to define requirements',
                'Write clean, maintainable, and efficient code',
                'Perform code reviews and mentor junior developers',
                'Troubleshoot and debug production issues',
                'Participate in agile ceremonies and planning sessions',
            ]),
            JobRequisitionItemTypeEnum::RequiredQualification => fake()->randomElement([
                "Bachelor's degree in Computer Science or related field",
                '3+ years of experience in software development',
                'Strong knowledge of PHP and Laravel framework',
                'Experience with relational databases (PostgreSQL/MySQL)',
                'Familiarity with modern frontend frameworks (Vue/React)',
                'Excellent problem-solving and analytical skills',
            ]),
            JobRequisitionItemTypeEnum::PreferredQualification => fake()->randomElement([
                'Experience with AWS or other cloud providers',
                'Knowledge of Docker and Kubernetes',
                'Contributions to open-source projects',
                'Experience with microservices architecture',
                'Strong communication skills',
                'Experience with CI/CD pipelines',
            ]),
            JobRequisitionItemTypeEnum::Benefit => fake()->randomElement([
                'Competitive salary and performance bonuses',
                'Health, dental, and vision insurance',
                'Flexible work hours and remote work options',
                'Professional development budget',
                'Modern office with snacks and drinks',
                'Generous PTO and parental leave',
            ]),
        };
    }
}
