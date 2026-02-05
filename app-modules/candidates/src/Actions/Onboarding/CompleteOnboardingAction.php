<?php

declare(strict_types=1);

namespace He4rt\Candidates\Actions\Onboarding;

use He4rt\Candidates\AiAutocompleteInterface;
use He4rt\Candidates\DTOs\CandidateEducationDTO;
use He4rt\Candidates\DTOs\CandidateOnboardingDTO;
use He4rt\Candidates\DTOs\CandidateWorkExperienceDTO;
use He4rt\Candidates\Enums\ResumeAnalyzeStatus;
use He4rt\Candidates\Events\AnalyzeResumeEvent;
use He4rt\Candidates\Exceptions\OnboardingException;
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
     * @throws OnboardingException
     */
    public function execute(TemporaryUploadedFile $file, string $userId): CandidateOnboardingDTO
    {
        /** @var Response $response */
        $response = Prism::structured()
            ->using(config('ai.provider.gemini.enum'), config('ai.provider.gemini.model'))
            ->withSchema($this->structureSchema())
            ->withPrompt(
                <<<'PROMPT'
                           Você é um assistente de triagem de currículos. Analise o arquivo anexo:

                            ### CRITÉRIOS DE REJEIÇÃO (is_cv: FALSE):
                            1. **Tipo de Arquivo**: Se NÃO for um currículo, perfil profissional ou certificado.
                            2. **Extensão/Tamanho**: Se o documento tiver MAIS DE 3 PÁGINAS, rejeite-o.
                            3. **Conteúdo**: Documentos fiscais, fotos pessoais ou textos sem nexo profissional.

                            ### JUSTIFICATIVA (rejection_reason):
                            - Se tiver mais de 5 páginas, escreva: "O arquivo é muito longo. Envie um currículo com no máximo 3 páginas."
                            - Se não for um currículo, escreva: "O arquivo enviado não é um currículo".

                            ### EXTRAÇÃO (Se is_cv: TRUE):
                            - Extraia até 5 experiências profissionais e a formação acadêmica.
PROMPT,
                [
                    Document::fromRawContent(
                        rawContent: $file->get(),
                        mimeType: $file->getMimeType()
                    ),
                ]
            )
            ->asStructured();

        $output = $response->structured;
        $this->validate($output, $userId);
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
            'work_experiences' => $workExperiences,
        ]);

    }

    private function structureSchema(): ObjectSchema
    {
        return new ObjectSchema(
            'cv_data',
            'Dados extraídos do currículo',
            /** @phpstan-ignore-next-line argument.type */
            [
                'is_cv' => new BooleanSchema(
                    'is_cv',
                    'Define se o arquivo é um currículo válido e tem 5 páginas ou menos.'
                ),

                'rejection_reason' => new StringSchema(
                    'rejection_reason',
                    'Motivo da rejeição em português (ex: arquivo muito longo, ou não é um currículo).'
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

    /**
     * @throws OnboardingException
     */
    private function validate(array $output, string $userId): void
    {
        if ($output['is_cv'] === true) {
            return;
        }

        $reason = $output['rejection_reason'] ?? '';

        if (str_contains($reason, 'muito longo') || str_contains($reason, '3 páginas')) {
            broadcast(new AnalyzeResumeEvent(ResumeAnalyzeStatus::Error, ['message' => 'O arquivo é muito longo. Envie um currículo com no máximo 3 páginas.'], $userId));
            throw OnboardingException::toExpensive();
        }

        broadcast(new AnalyzeResumeEvent(ResumeAnalyzeStatus::Error, ['message' => 'Arquivo enviado não é um currículo.'], $userId));

        throw OnboardingException::invalidCv();
    }
}
