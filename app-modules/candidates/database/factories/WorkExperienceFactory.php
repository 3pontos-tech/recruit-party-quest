<?php

declare(strict_types=1);

namespace He4rt\Candidates\Database\Factories;

use He4rt\Candidates\Models\Candidate;
use He4rt\Candidates\Models\WorkExperience;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;

/** @extends Factory<WorkExperience> */
class WorkExperienceFactory extends Factory
{
    protected $model = WorkExperience::class;

    public function definition(): array
    {
        $companies = [
            'Nubank',
            'Stone Pagamentos',
            'PicPay',
            'Magazine Luiza',
            'iFood',
            'Globo.com',
            'B2W Digital',
            'Mercado Livre',
            'Banco Inter',
            'GetNinjas',
        ];

        $positions = [
            'Desenvolvedor Full Stack',
            'Desenvolvedor Front-end React',
            'Desenvolvedor Back-end PHP',
            'Designer UX/UI',
            'Product Manager',
            'Tech Lead',
            'Engenheiro de Software',
            'Desenvolvedor Mobile',
        ];

        $technologies = [
            ['React', 'TypeScript', 'Node.js'],
            ['Vue.js', 'Laravel', 'MySQL'],
            ['PHP', 'Symfony', 'PostgreSQL'],
            ['Python', 'Django', 'Redis'],
            ['Java', 'Spring Boot', 'MongoDB'],
            ['React Native', 'Expo', 'Firebase'],
            ['Angular', 'NestJS', 'Docker'],
            ['Flutter', 'Dart', 'GraphQL'],
        ];

        $yearsAgo = fake()->numberBetween(0, 4);
        $monthsAgo = fake()->numberBetween(0, 11);
        $startDate = Date::now()->subYears($yearsAgo)->subMonths($monthsAgo);

        $experienceDurationMonths = fake()->numberBetween(6, 36);
        $endDate = $startDate->copy()->addMonths($experienceDurationMonths);

        $isCurrentlyWorking = fake()->boolean(30);

        if ($isCurrentlyWorking) {
            $endDate = null;
        } elseif ($endDate->isFuture()) {
            $endDate = Date::now()->subMonths(fake()->numberBetween(1, 24));
        }

        $position = fake()->randomElement($positions);
        $techStack = fake()->randomElement($technologies);
        $teamSize = fake()->numberBetween(3, 15);

        return [
            'company_name' => fake()->randomElement($companies),
            'description' => $this->generateJobDescription($position, $techStack),
            'start_date' => $startDate,
            'end_date' => $isCurrentlyWorking ? null : $endDate,
            'is_currently_working_here' => $isCurrentlyWorking,
            'metadata' => [
                'position' => $position,
                'technologies' => $techStack,
                'team_size' => $teamSize,
                'project_type' => fake()->randomElement([
                    'E-commerce',
                    'Fintech',
                    'EdTech',
                    'HealthTech',
                    'Marketplace',
                    'SaaS',
                ]),
            ],
            'created_at' => Date::now(),
            'updated_at' => Date::now(),

            'candidate_id' => Candidate::factory(),
        ];
    }

    private function generateJobDescription(
        string $position,
        array $technologies
    ): string {
        $techString = implode(', ', $technologies);

        $descriptions = [
            sprintf('Desenvolvimento de aplicações web utilizando %s. Responsável por implementar novas funcionalidades e manter a qualidade do código através de code reviews e testes automatizados.',
                $techString),
            sprintf('Atuação como %s em projetos de alta performance. Trabalho em equipe ágil utilizando metodologias Scrum, com foco em %s e boas práticas de desenvolvimento.',
                $position, $techString),
            sprintf('Desenvolvimento e manutenção de sistemas utilizando %s. Participação ativa no ciclo completo de desenvolvimento, desde a concepção até o deploy em produção.',
                $techString),
            sprintf('Responsável pelo desenvolvimento de interfaces e APIs utilizando %s. Colaboração próxima com equipes de produto e design para entregar soluções de alta qualidade.',
                $techString),
        ];

        return fake()->randomElement($descriptions);
    }
}
