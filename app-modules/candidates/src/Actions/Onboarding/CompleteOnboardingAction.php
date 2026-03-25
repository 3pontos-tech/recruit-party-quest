<?php

declare(strict_types=1);

namespace He4rt\Candidates\Actions\Onboarding;

use He4rt\Candidates\AiAutocompleteInterface;
use He4rt\Candidates\DTOs\CandidateEducationDTO;
use He4rt\Candidates\DTOs\CandidateOnboardingDTO;
use He4rt\Candidates\DTOs\CandidateWorkExperienceDTO;
use He4rt\Candidates\Enums\ResumeErrorReasons;
use He4rt\Candidates\Exceptions\OnboardingException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Exceptions\PrismRateLimitedException;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\BooleanSchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;
use Prism\Prism\Structured\Response;
use Prism\Prism\ValueObjects\Media\Document;

final readonly class CompleteOnboardingAction implements AiAutocompleteInterface
{
    public function __construct(
        private ResumeErrorReasons $notAnCv = ResumeErrorReasons::NotAnCV,
    ) {}

    /**
     * @throws FileNotFoundException
     * @throws OnboardingException
     */
    public function execute(TemporaryUploadedFile $file): CandidateOnboardingDTO
    {
        $provider = config('ai.provider.gemini.enum');
        $models = [
            config('ai.provider.gemini.model'),
            config('ai.provider.gemini.fallback_model'),
        ];

        $output = $this->callWithFallback($file, $provider, $models);

        return CandidateOnboardingDTO::make([
            'education' => array_map(
                CandidateEducationDTO::make(...),
                $output['education']
            ),
            'work_experiences' => array_map(
                CandidateWorkExperienceDTO::make(...),
                $output['work_experiences']
            ),
        ]);
    }

    /**
     * @param  array<string>  $models
     * @return array<string, mixed>
     *
     * @throws FileNotFoundException
     * @throws OnboardingException
     */
    private function callWithFallback(TemporaryUploadedFile $file, string $provider, array $models): array
    {
        $lastException = null;

        foreach ($models as $model) {
            try {
                return $this->callPrism($file, $provider, $model);
            } catch (PrismRateLimitedException $e) {
                logger()->warning('Gemini rate limit hit, trying next model', [
                    'model' => $model,
                    'retry_after' => $e->retryAfter,
                ]);
                $lastException = $e;

                continue;
            } catch (PrismException $e) {
                logger()->error('Prism non-recoverable error during CV analysis', [
                    'model' => $model,
                    'error' => $e->getMessage(),
                ]);
                throw OnboardingException::rateLimiting();
            }
        }

        throw OnboardingException::rateLimiting(previous: $lastException);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws FileNotFoundException
     * @throws OnboardingException
     * @throws PrismRateLimitedException
     * @throws PrismException
     */
    private function callPrism(TemporaryUploadedFile $file, string $provider, string $model): array
    {
        /** @var Response $response */
        $response = Prism::structured()
            ->using($provider, $model)
            ->withSchema($this->structureSchema())
            ->withPrompt(
                <<<PROMPT
                       Você é um assistente de triagem de currículos. Analise o arquivo anexo:

                        ### CRITÉRIOS DE REJEIÇÃO (is_cv: FALSE):
                        1. **Tipo de Arquivo**: Se NÃO for um currículo, perfil profissional ou certificado.
                        2. **Conteúdo**: Documentos fiscais, fotos pessoais ou textos sem nexo profissional.

                        ### JUSTIFICATIVA (rejection_reason):
                        - Se não for um currículo, escreva: "{$this->notAnCv->value}"

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
        $this->validate($output);

        return $output;
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
                    'Define se o arquivo é um currículo válido.'
                ),

                'rejection_reason' => new StringSchema(
                    'rejection_reason',
                    sprintf('Motivo da rejeição seguindo esses padrões (ex: {%s}).', $this->notAnCv->value)
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
     * @param  array<string, mixed>  $output
     *
     * @throws OnboardingException
     */
    private function validate(array $output): void
    {
        if ($output['is_cv'] === true) {
            return;
        }

        $reason = $output['rejection_reason'] ?? '';

        if (str_contains($reason, $this->notAnCv->value)) {
            throw OnboardingException::invalidCv();
        }

    }
}
