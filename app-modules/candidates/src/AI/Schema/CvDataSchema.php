<?php

declare(strict_types=1);

namespace He4rt\Candidates\AI\Schema;

use He4rt\Candidates\Enums\ResumeErrorReasons;
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\BooleanSchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;

final class CvDataSchema
{
    public static function make(ResumeErrorReasons $notAnCv): ObjectSchema
    {
        return new ObjectSchema(
            'cv_data',
            'Dados extraídos do currículo',
            /** @phpstan-ignore-next-line argument.type */
            [
                'is_cv' => new BooleanSchema(
                    'is_cv',
                    'Define se o arquivo é um currículo válido.'
                ),
                'rejection_reason' => new StringSchema(
                    'rejection_reason',
                    sprintf('Motivo da rejeição seguindo esses padrões (ex: {%s}).', $notAnCv->value)
                ),
                'work_experiences' => new ArraySchema(
                    'work_experiences',
                    'Lista de experiências profissionais',
                    new ObjectSchema(
                        'experience',
                        'Detalhes da experiência',
                        /** @phpstan-ignore-next-line argument.type */
                        [
                            'company_name' => new StringSchema('company_name', 'Nome da empresa'),
                            'position' => new StringSchema('position', 'Cargo ou função exercida, exatamente como aparece no currículo (ex.: Analista de RH Pleno)'),
                            'description' => new StringSchema('description', 'Descrição das atividades'),
                            'skills' => new ArraySchema(
                                'skills',
                                'Competências, tecnologias e ferramentas citadas nesta experiência',
                                new StringSchema('skill', 'Nome da competência')
                            ),
                            'start_date' => new StringSchema('start_date', 'Data de início no formato YYYY-MM-DD (mês e ano viram o dia 01 do mês; só o ano vira 01/01), ou null se o currículo não informar — nunca texto como "N/A"', nullable: true),
                            'end_date' => new StringSchema('end_date', 'Data de término no formato YYYY-MM-DD (mês e ano viram o dia 01 do mês; só o ano vira 01/01), ou null se ainda estiver no cargo ou o currículo não informar — nunca texto como "N/A"', nullable: true),
                            'is_currently_working_here' => new BooleanSchema('is_currently_working_here', 'Se trabalha lá'),
                        ],
                        requiredFields: ['company_name', 'start_date', 'is_currently_working_here'],
                    )
                ),
                'education' => new ArraySchema(
                    'education',
                    'Lista de formação acadêmica',
                    new ObjectSchema(
                        'education_item',
                        'Detalhes da formação',
                        /** @phpstan-ignore-next-line argument.type */
                        [
                            'institution' => new StringSchema('institution', 'Nome da instituição'),
                            'degree' => new StringSchema('degree', 'Grau acadêmico'),
                            'field_of_study' => new StringSchema('field_of_study', 'Curso'),
                            'start_date' => new StringSchema('start_date', 'Data de início no formato YYYY-MM-DD (mês e ano viram o dia 01 do mês; só o ano vira 01/01), ou null se o currículo não informar — nunca texto como "N/A"', nullable: true),
                            'end_date' => new StringSchema('end_date', 'Data de término no formato YYYY-MM-DD (mês e ano viram o dia 01 do mês; só o ano vira 01/01), ou null se ainda estiver cursando ou o currículo não informar — nunca texto como "N/A"', nullable: true),
                            'is_enrolled' => new BooleanSchema('is_enrolled', 'Se ainda está cursando'),
                        ],
                        requiredFields: ['institution', 'start_date', 'is_enrolled'],
                    )
                ),
            ],
            requiredFields: ['is_cv'],
        );
    }
}
