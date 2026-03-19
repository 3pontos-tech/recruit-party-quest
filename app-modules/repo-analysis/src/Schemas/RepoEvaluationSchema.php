<?php

declare(strict_types=1);

namespace He4rt\RepoAnalysis\Schemas;

use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\EnumSchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;

class RepoEvaluationSchema
{
    public static function build(): ObjectSchema
    {
        return new ObjectSchema(
            name: 'evaluation',
            description: 'Avaliação detalhada do repositório',
            properties: [
                new StringSchema('summary', 'Resumo executivo do projeto e sua qualidade geral'),
                new ObjectSchema(
                    name: 'highlights',
                    description: 'Pontos de destaque',
                    properties: [
                        new ArraySchema(
                            name: 'strengths',
                            description: 'Lista de pontos fortes encontrados',
                            items: new StringSchema('strength', 'Ponto forte')
                        ),
                        new ArraySchema(
                            name: 'main_risks',
                            description: 'Lista de riscos ou débitos técnicos urgentes',
                            items: new StringSchema('risk', 'Risco ou débito técnico')
                        ),
                    ],
                    requiredFields: ['strengths', 'main_risks']
                ),
                new ArraySchema(
                    name: 'categories',
                    description: 'Avaliação dividida por categorias',
                    items: new ObjectSchema(
                        name: 'category',
                        description: 'Uma categoria avaliada (ex: Architecture, Security, Testing)',
                        properties: [
                            new StringSchema('name', 'Nome da categoria'),
                            new StringSchema('context', 'Contexto geral sobre como o repositório lidou com essa categoria'),
                            new ArraySchema(
                                name: 'problems',
                                description: 'Problemas encontrados nesta categoria',
                                items: new ObjectSchema(
                                    name: 'problem',
                                    description: 'Um problema específico',
                                    properties: [
                                        new StringSchema('title', 'Título curto do problema'),
                                        new StringSchema('description', 'Descrição com exemplos do código analisado'),
                                        new EnumSchema(
                                            name: 'impact',
                                            description: 'Impacto no projeto',
                                            options: ['low', 'medium', 'high']
                                        ),
                                        new StringSchema('why_it_matters', 'Por que isso é um problema e deve ser corrigido'),
                                    ],
                                    requiredFields: ['title', 'description', 'impact', 'why_it_matters']
                                )
                            ),
                            new ArraySchema(
                                name: 'suggestions',
                                description: 'Sugestões de melhoria acionáveis',
                                items: new StringSchema('suggestion', 'Sugestão')
                            ),
                            new ArraySchema(
                                name: 'study_topics',
                                description: 'Tópicos de estudo recomendados para o candidato',
                                items: new StringSchema('topic', 'Tópico de estudo')
                            ),
                        ],
                        requiredFields: ['name', 'context', 'problems', 'suggestions', 'study_topics']
                    )
                ),
                new ObjectSchema(
                    name: 'detected_stack',
                    description: 'Stack tecnológica',
                    properties: [
                        new StringSchema('language', 'Linguagem predominante'),
                        new StringSchema('framework', 'Framework utilizado, se houver'),
                        new StringSchema('architecture', 'Padrão arquitetural identificado (MVC, Hexagonal, etc)'),
                        new ArraySchema(
                            name: 'main_dependencies',
                            description: 'Principais pacotes/libs utilizadas',
                            items: new StringSchema('dependency', 'Dependência')
                        ),
                    ],
                    requiredFields: ['language', 'framework', 'architecture', 'main_dependencies']
                ),
            ],
            requiredFields: ['summary', 'highlights', 'categories', 'detected_stack']
        );
    }
}
