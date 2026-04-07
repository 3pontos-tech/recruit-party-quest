<?php

declare(strict_types=1);

namespace He4rt\Candidates\Actions\Onboarding;

use He4rt\Candidates\AI\Prompts\CvAnalysisPrompt;
use He4rt\Candidates\AI\Schema\CvDataSchema;
use He4rt\Candidates\AiAutocompleteInterface;
use He4rt\Candidates\DTOs\CandidateEducationDTO;
use He4rt\Candidates\DTOs\CandidateOnboardingDTO;
use He4rt\Candidates\DTOs\CandidateWorkExperienceDTO;
use He4rt\Candidates\Enums\ResumeErrorReasons;
use He4rt\Candidates\Exceptions\OnboardingException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Cache;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Exceptions\PrismRateLimitedException;
use Prism\Prism\Facades\Prism;
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
                $output['education'] ?? []
            ),
            'work_experiences' => array_map(
                CandidateWorkExperienceDTO::make(...),
                $output['work_experiences'] ?? []
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
            $circuitKey = 'cb:gemini:'.$model;

            if (Cache::has($circuitKey)) {
                logger()->info('Circuit breaker active, skipping model', [
                    'model' => $model,
                    'circuit_key' => $circuitKey,
                ]);

                continue;
            }

            try {
                return $this->callPrism($file, $provider, $model);
            } catch (PrismRateLimitedException $e) {
                logger()->warning('Gemini rate limit hit, opening circuit breaker', [
                    'model' => $model,
                    'retry_after' => $e->retryAfter,
                    'circuit_key' => $circuitKey,
                    'error' => $e->getMessage(),
                ]);
                Cache::put($circuitKey, true, now()->addMinutes(3));
                $lastException = $e;

                continue;
            } catch (PrismException $e) {
                logger()->error('Prism error, opening circuit breaker', [
                    'model' => $model,
                    'circuit_key' => $circuitKey,
                    'error' => $e->getMessage(),
                    'exception' => $e::class,
                ]);
                Cache::put($circuitKey, true, now()->addMinutes(3));
                $lastException = $e;

                continue;
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
            ->withSchema(CvDataSchema::make($this->notAnCv))
            ->withPrompt(
                CvAnalysisPrompt::make($this->notAnCv),
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
