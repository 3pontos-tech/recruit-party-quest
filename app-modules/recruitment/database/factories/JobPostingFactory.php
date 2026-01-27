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
            'about_company' => '3Pontos is a leading technology company dedicated to empowering developers and businesses through innovation and collaboration.',
            'about_team' => 'The engineering team is a diverse group of passionate individuals committed to technical excellence and continuous improvement.',
            'work_schedule' => 'Flexible, Monday to Friday',
            'accessibility_accommodations' => 'Our office is fully wheelchair accessible and we provide assistive technologies as needed.',
            'is_disability_confident' => fake()->boolean(),
            'external_post_url' => fake()->url(),
            'created_at' => Date::now(),
            'updated_at' => Date::now(),

            'job_requisition_id' => JobRequisition::factory(),
            'team_id' => Team::factory(),
        ];
    }
}
