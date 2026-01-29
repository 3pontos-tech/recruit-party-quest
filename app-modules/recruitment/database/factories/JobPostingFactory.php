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
            'description' => sprintf(
                'Join our team as a %s and become part of an environment focused on collaboration, continuous learning, and technical excellence.
                In this role, you will work closely with multidisciplinary teams to design, develop, and evolve solutions that directly impact our products and users.

                You will be encouraged to propose improvements, participate in technical discussions, and contribute to architectural decisions, always aiming for clean, maintainable, and scalable code.
                We value professionals who are curious, proactive, and committed to delivering high-quality results.

                You will have the opportunity to work with modern technologies, agile methodologies, and best development practices, while continuously improving your skills through real challenges and meaningful projects.
                If you enjoy solving complex problems, learning new tools, and growing alongside an experienced team, this position offers an excellent opportunity for professional development and long-term growth.',
                $title
            ),
            'external_post_url' => fake()->url(),
            'created_at' => Date::now(),
            'updated_at' => Date::now(),

            'job_requisition_id' => JobRequisition::factory(),
            'team_id' => Team::factory(),
        ];
    }
}
