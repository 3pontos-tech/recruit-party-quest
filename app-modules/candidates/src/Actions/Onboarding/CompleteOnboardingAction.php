<?php

declare(strict_types=1);

namespace He4rt\Candidates\Actions\Onboarding;

use He4rt\Candidates\AI\Prompts\CvAnalysisPrompt;
use He4rt\Candidates\AI\Schema\CvDataSchema;
use He4rt\Candidates\AiAutocompleteInterface;
use He4rt\Candidates\DTOs\CandidateOnboardingDTO;
use He4rt\Candidates\Enums\ResumeErrorReasons;
use He4rt\Candidates\Exceptions\OnboardingException;
use He4rt\Candidates\Exceptions\ProvidersUnavailableException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Cache;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Exceptions\PrismProviderOverloadedException;
use Prism\Prism\Exceptions\PrismRateLimitedException;
use Prism\Prism\Exceptions\PrismRequestTooLargeException;
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
            'education' => $output['education'] ?? [],
            'work_experiences' => $output['work_experiences'] ?? [],
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
            } catch (PrismProviderOverloadedException $e) {
                logger()->warning('Prism transient error, opening circuit breaker', [
                    'model' => $model,
                    'circuit_key' => $circuitKey,
                    'error' => $e->getMessage(),
                    'exception' => $e::class,
                ]);
                Cache::put($circuitKey, true, now()->addMinutes(3));
                $lastException = $e;

                continue;
            } catch (PrismRequestTooLargeException $e) {
                logger()->warning('Prism request too large, skipping model without opening circuit breaker', [
                    'model' => $model,
                    'error' => $e->getMessage(),
                    'exception' => $e::class,
                ]);
                $lastException = $e;

                continue;
            } catch (PrismException $e) {
                logger()->error('Prism permanent error, not opening circuit breaker', [
                    'model' => $model,
                    'error' => $e->getMessage(),
                    'exception' => $e::class,
                ]);
                $lastException = $e;

                continue;
            }
        }

        $openCircuits = array_values(array_filter(
            $models,
            fn (string $model): bool => Cache::has('cb:gemini:'.$model),
        ));

        if (count($openCircuits) === count($models)) {
            logger()->error('Every Gemini circuit breaker is open, aborting without retry', [
                'models' => $openCircuits,
                'last_error' => $lastException?->getMessage(),
            ]);

            throw ProvidersUnavailableException::make(previous: $lastException);
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
        $rawContent = $file->get();

        if (! is_string($rawContent)) {
            throw new FileNotFoundException(sprintf('Unable to read uploaded file [%s].', $file->getClientOriginalName()));
        }

        /** @var Response $response */
        $response = Prism::structured()
            ->using($provider, $model)
            ->withClientOptions(['timeout' => 70, 'connect_timeout' => 10])
            ->withSchema(CvDataSchema::make($this->notAnCv))
            ->withPrompt(
                CvAnalysisPrompt::make($this->notAnCv),
                [
                    Document::fromRawContent(
                        rawContent: $rawContent,
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
     * Only a response that explicitly flags the file as a CV is accepted.
     *
     * The schema declares `is_cv` as required, so a missing or non-boolean flag means the
     * provider broke the contract — rejecting it keeps a malformed response from silently
     * finishing the onboarding with an empty DTO.
     *
     * @param  array<string, mixed>  $output
     *
     * @throws OnboardingException
     */
    private function validate(array $output): void
    {
        if (($output['is_cv'] ?? null) === true) {
            return;
        }

        throw OnboardingException::invalidCv();
    }
}
