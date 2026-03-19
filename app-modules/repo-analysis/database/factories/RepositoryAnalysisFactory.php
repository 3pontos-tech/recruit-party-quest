<?php

declare(strict_types=1);

namespace He4rt\RepoAnalysis\Database\Factories;

use He4rt\Candidates\Models\Candidate;
use He4rt\RepoAnalysis\Enums\AnalysisStatus;
use He4rt\RepoAnalysis\Models\RepositoryAnalysis;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RepositoryAnalysis> */
class RepositoryAnalysisFactory extends Factory
{
    protected $model = RepositoryAnalysis::class;

    public function definition(): array
    {
        $username = fake()->userName();
        $repoName = fake()->slug(2);

        return [
            'candidate_id' => Candidate::factory(),
            'repo_name' => $repoName,
            'repo_full_name' => sprintf('%s/%s', $username, $repoName),
            'repo_url' => sprintf('https://github.com/%s/%s', $username, $repoName),
            'repo_default_branch' => 'main',
            'repo_language' => fake()->randomElement(['PHP', 'JavaScript', 'Python', 'TypeScript', null]),
            'repo_is_private' => fake()->boolean(20),
            'status' => AnalysisStatus::Pending,
            'analyzed_at' => null,
            'result' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => AnalysisStatus::Completed,
            'analyzed_at' => now()->subDays(fake()->numberBetween(1, 30)),
            'result' => [
                'summary' => fake()->paragraph(),
                'highlights' => [
                    'strengths' => ['Clean code structure'],
                    'main_risks' => ['Low test coverage'],
                ],
                'detected_stack' => [
                    'language' => 'PHP',
                    'framework' => 'Laravel',
                    'architecture' => 'MVC',
                    'main_dependencies' => ['filament', 'livewire'],
                ],
                'categories' => [],
            ],
        ]);
    }

    public function analyzing(): static
    {
        return $this->state(fn () => [
            'status' => AnalysisStatus::Analyzing,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status' => AnalysisStatus::Failed,
        ]);
    }

    public function withFullResult(): static
    {
        return $this->state(fn () => [
            'status' => AnalysisStatus::Completed,
            'analyzed_at' => now()->subDays(fake()->numberBetween(1, 30)),
            'result' => [
                'summary' => 'O código apresenta boa estrutura geral com uso adequado do framework Laravel. Foram identificados pontos de melhoria relacionados à performance de consultas ao banco de dados e cobertura de testes. A arquitetura segue boas práticas mas pode ser otimizada.',
                'highlights' => [
                    'strengths' => [
                        'Uso consistente dos padrões do Laravel',
                        'Boa separação de responsabilidades nas camadas',
                    ],
                    'main_risks' => [
                        'Cobertura de testes abaixo de 40%',
                        'Presença de queries N+1 em endpoints críticos',
                    ],
                ],
                'detected_stack' => [
                    'language' => 'PHP',
                    'framework' => 'Laravel',
                    'architecture' => 'MVC',
                    'main_dependencies' => [
                        'filament/filament',
                        'livewire/livewire',
                        'pestphp/pest',
                        'spatie/laravel-permission',
                    ],
                ],
                'categories' => [
                    [
                        'name' => 'Arquitetura e Design',
                        'context' => 'O projeto utiliza MVC de forma consistente, mas há oportunidades de melhoria na separação de lógica de negócio.',
                        'problems' => [
                            [
                                'title' => 'Queries N+1 em controllers',
                                'description' => 'Encontradas em UserController@index e OrderController@show — o relacionamento não é carregado com eager loading.',
                                'impact' => 'high',
                                'why_it_matters' => 'Em produção com volume de dados, isso causa lentidão exponencial e pode derrubar o banco de dados.',
                            ],
                            [
                                'title' => 'Lógica de negócio em controllers',
                                'description' => 'O método store() do InvoiceController contém mais de 80 linhas de lógica de negócio acoplada.',
                                'impact' => 'medium',
                                'why_it_matters' => 'Dificulta testes unitários e reutilização da lógica em outros pontos da aplicação.',
                            ],
                        ],
                        'suggestions' => [
                            'Implementar eager loading com with() nas consultas',
                            'Extrair lógica de negócio para Service classes',
                        ],
                        'study_topics' => [
                            'Design Patterns',
                            'SOLID Principles',
                            'Repository Pattern',
                        ],
                    ],
                    [
                        'name' => 'Testes e Qualidade',
                        'context' => 'Existem testes, mas a cobertura é baixa e os cenários de erro não são validados.',
                        'problems' => [
                            [
                                'title' => 'Cobertura de testes abaixo de 40%',
                                'description' => 'Apenas os fluxos felizes dos controllers estão cobertos. Services e Jobs não possuem testes.',
                                'impact' => 'high',
                                'why_it_matters' => 'Sem cobertura adequada, regressões passam despercebidas até chegar em produção.',
                            ],
                        ],
                        'suggestions' => [
                            'Adicionar testes unitários para Services',
                            'Implementar testes de integração com Pest',
                        ],
                        'study_topics' => [
                            'Test-Driven Development',
                            'Integration Testing',
                        ],
                    ],
                ],
            ],
        ]);
    }
}
