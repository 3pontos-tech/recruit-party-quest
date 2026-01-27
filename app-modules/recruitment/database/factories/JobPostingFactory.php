<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Database\Factories;

use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Teams\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;

/** @extends Factory<JobPosting> */
class JobPostingFactory extends Factory
{
    protected $model = JobPosting::class;

    public function definition(): array
    {
        $jobTitles = [
            'Software Engineer',
            'Senior Laravel Developer',
            'Product Manager',
            'UX/UI Designer',
            'Data Scientist',
            'DevOps Engineer',
            'Frontend Developer',
            'Backend Engineer',
            'QA Specialist',
            'Technical Lead',
        ];

        $title = fake()->randomElement($jobTitles);

        return [
            'title' => $title,
            'slug' => str($title)->slug()->toString(),
            'summary' => 'Join our team as a '.$title.' and help us build amazing products using modern technologies.',
            'description' => [
                'We are looking for a passionate '.$title.' to join our growing engineering team.',
                'You will be responsible for designing, developing, and maintaining high-quality software solutions.',
                'Our ideal candidate has a strong background in software development and a desire to learn and grow.',
            ],
            'responsibilities' => [
                'Design and implement scalable software solutions',
                'Collaborate with cross-functional teams to define requirements',
                'Write clean, maintainable, and efficient code',
                'Perform code reviews and mentor junior developers',
                'Troubleshoot and debug production issues',
            ],
            'required_qualifications' => [
                "Bachelor's degree in Computer Science or related field",
                '3+ years of experience in software development',
                'Strong knowledge of PHP and Laravel framework',
                'Experience with relational databases (PostgreSQL/MySQL)',
                'Familiarity with modern frontend frameworks (Vue/React)',
            ],
            'preferred_qualifications' => [
                'Experience with AWS or other cloud providers',
                'Knowledge of Docker and Kubernetes',
                'Contributions to open-source projects',
                'Excellent communication and problem-solving skills',
            ],
            'benefits' => [
                'Competitive salary and performance bonuses',
                'Health, dental, and vision insurance',
                'Flexible work hours and remote work options',
                'Professional development budget',
                'Modern office with snacks and drinks',
            ],
            'external_post_url' => fake()->url(),
            'created_at' => Date::now(),
            'updated_at' => Date::now(),

            'job_requisition_id' => JobRequisition::factory(),
            'team_id' => Team::factory(),
        ];
    }
}
