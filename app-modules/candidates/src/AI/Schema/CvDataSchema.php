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

                    /** @phpstan-ignore-next-line argument.type */
                    new ObjectSchema('experience', 'Detalhes da experiência', [
                        'company_name' => new StringSchema('company_name', 'Nome da empresa'),
                        'description' => new StringSchema('description', 'Descrição das atividades'),
                        'start_date' => new StringSchema('start_date', 'Data de início YYYY-MM-DD'),
                        'end_date' => new StringSchema('end_date', 'Data de término YYYY-MM-DD ou null'),
                        'is_currently_working_here' => new BooleanSchema('is_currently_working_here', 'Se trabalha lá'),
                    ])
                ),
                'education' => new ArraySchema(
                    'education',
                    'Lista de formação acadêmica',

                    /** @phpstan-ignore-next-line argument.type */
                    new ObjectSchema('education_item', 'Detalhes da formação', [
                        'institution' => new StringSchema('institution', 'Nome da instituição'),
                        'degree' => new StringSchema('degree', 'Grau acadêmico'),
                        'field_of_study' => new StringSchema('field_of_study', 'Curso'),
                        'start_date' => new StringSchema('start_date', 'Data de início YYYY-MM-DD'),
                        'end_date' => new StringSchema('end_date', 'Data de término YYYY-MM-DD'),
                        'is_enrolled' => new BooleanSchema('is_enrolled', 'Se ainda está cursando'),
                    ])
                ),
            ]
        );
    }
}
