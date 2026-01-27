<?php

declare(strict_types=1);

namespace He4rt\Candidates\Database\Factories;

use He4rt\Candidates\Models\Candidate;
use He4rt\Candidates\Models\Education;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;

/** @extends Factory<Education> */
class EducationFactory extends Factory
{
    protected $model = Education::class;

    public function definition(): array
    {
        $institutions = [
            'Universidade de São Paulo (USP)',
            'Universidade Estadual de Campinas (UNICAMP)',
            'Pontifícia Universidade Católica de São Paulo (PUC-SP)',
            'Universidade Federal do Rio de Janeiro (UFRJ)',
            'Universidade Presbiteriana Mackenzie',
            'Fundação Getulio Vargas (FGV)',
        ];

        $degrees = [
            'Bacharelado',
            'Tecnólogo',
            'Pós-graduação',
            'MBA',
            'Mestrado',
        ];

        $fieldsOfStudy = [
            'Ciência da Computação',
            'Engenharia de Software',
            'Sistemas de Informação',
            'Design Digital',
            'Administração',
            'Marketing Digital',
            'Engenharia da Computação',
            'Análise e Desenvolvimento de Sistemas',
        ];

        $yearsAgo = fake()->numberBetween(2, 6);
        $startDate = Date::now()->subYears($yearsAgo);

        $degree = fake()->randomElement($degrees);
        $courseDuration = match ($degree) {
            'Bacharelado' => fake()->numberBetween(4, 5),
            'Tecnólogo', 'Mestrado' => fake()->numberBetween(2, 3),
            'Pós-graduação', 'MBA' => fake()->numberBetween(1, 2),
            default => 4,
        };

        $endDate = $startDate->copy()->addYears($courseDuration);
        $isEnrolled = $endDate->isFuture() || fake()->boolean();

        return [
            'institution' => fake()->randomElement($institutions),
            'degree' => $degree,
            'field_of_study' => fake()->randomElement($fieldsOfStudy),
            'start_date' => $startDate,
            'end_date' => $isEnrolled ? null : $endDate,
            'is_enrolled' => $isEnrolled,
            'created_at' => Date::now(),
            'updated_at' => Date::now(),

            'candidate_id' => Candidate::factory(),
        ];
    }
}
