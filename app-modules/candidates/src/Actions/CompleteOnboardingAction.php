<?php

declare(strict_types=1);

namespace He4rt\Candidates\Actions;

use He4rt\Candidates\AiAutocompleteInterface;
use He4rt\Candidates\DTOs\CandidateEducationDTO;
use He4rt\Candidates\DTOs\CandidateOnboardingDTO;
use He4rt\Candidates\DTOs\CandidateWorkExperienceDTO;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\BooleanSchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;
use Prism\Prism\Structured\Response;
use Prism\Prism\ValueObjects\Media\Document;

final class CompleteOnboardingAction implements AiAutocompleteInterface
{
    /**
     * @throws FileNotFoundException
     */
    public function execute(TemporaryUploadedFile $file): CandidateOnboardingDTO
    {
        dd(1);
        /** @var Response $response */
        $response = Prism::structured()
            ->using(config('ai.provider.gemini.enum'), config('ai.provider.gemini.model'))
            ->withSchema($this->structureSchema())
            ->withPrompt(
                'Extraia os dados do currículo anexado para preencher o formulário conforme o esquema.',
                [
                    Document::fromRawContent(
                        rawContent: $file->get(),
                        mimeType: $file->getMimeType()
                    ),
                ]
            )
            ->asStructured();

        $output = $response->structured;
        $workExperiences = [];
        $education = [];

        foreach ($output['work_experiences'] as $item) {
            $workExperiences[] = CandidateWorkExperienceDTO::make(
                $item
            );
        }

        foreach ($output['education'] as $item) {
            $education[] = CandidateEducationDTO::make(
                $item
            );
        }

        return CandidateOnboardingDTO::make([
            'education' => $education,
            'work' => $workExperiences,
        ]);

    }

    private function structureSchema(): ObjectSchema
    {
        return new ObjectSchema(
            'cv_data',
            'Dados extraídos do currículo',
            /** @phpstan-ignore-next-line argument.type */
            [
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
