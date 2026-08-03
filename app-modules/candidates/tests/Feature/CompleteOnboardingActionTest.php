<?php

declare(strict_types=1);

use He4rt\Candidates\Actions\Onboarding\CompleteOnboardingAction;
use He4rt\Candidates\DTOs\CandidateOnboardingDTO;
use He4rt\Candidates\Exceptions\OnboardingException;
use He4rt\Candidates\Exceptions\ProvidersUnavailableException;
use Illuminate\Support\Facades\Cache;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Prism\Prism\Enums\Provider as ProviderEnum;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Exceptions\PrismProviderOverloadedException;
use Prism\Prism\Exceptions\PrismRateLimitedException;
use Prism\Prism\Exceptions\PrismRequestTooLargeException;
use Prism\Prism\Facades\Prism;
use Prism\Prism\PrismManager;
use Prism\Prism\Structured\Request as StructuredRequest;
use Prism\Prism\Structured\Response as StructuredResponse;
use Prism\Prism\Testing\PrismFake;
use Prism\Prism\Testing\StructuredResponseFake;

use function Pest\Laravel\mock;

/** @return array<string, mixed> */
function onboardingValidCvStructured(): array
{
    return [
        'is_cv' => true,
        'rejection_reason' => '',
        'work_experiences' => [
            [
                'company_name' => '3-Pontos',
                'description' => 'Backend developer',
                'start_date' => '2022-01-01',
                'end_date' => null,
                'is_currently_working_here' => true,
            ],
        ],
        'education' => [
            [
                'institution' => 'FATEC',
                'degree' => 'Bacharelado',
                'field_of_study' => 'ADS',
                'start_date' => '2018-01-01',
                'end_date' => '2022-01-01',
                'is_enrolled' => false,
            ],
        ],
    ];
}

function onboardingMakeFakeFile(): TemporaryUploadedFile
{
    $file = mock(TemporaryUploadedFile::class);
    $file->shouldReceive('get')->andReturn('fake pdf content');
    $file->shouldReceive('getMimeType')->andReturn('application/pdf');

    return $file;
}

/**
 * Binds a custom PrismManager that throws PrismRateLimitedException on the first
 * structured() call and returns the given response on subsequent calls.
 */
function bindRateLimitOnPrimaryThenSuccessOnFallback(StructuredResponse $successResponse): PrismFake
{
    $fake = new class($successResponse) extends PrismFake
    {
        public int $callCount = 0;

        public function __construct(private readonly StructuredResponse $successResponse) {}

        public function structured(StructuredRequest $request): StructuredResponse
        {
            $this->callCount++;

            if ($this->callCount === 1) {
                throw PrismRateLimitedException::make();
            }

            return $this->successResponse;
        }
    };

    app()->instance(PrismManager::class, new class($fake) extends PrismManager
    {
        public function __construct(private readonly PrismFake $fake) {}

        public function resolve(ProviderEnum|string $name, array $providerConfig = []): PrismFake
        {
            return $this->fake;
        }
    });

    return $fake;
}

/** Binds a custom PrismManager that always throws PrismRateLimitedException. */
function bindAlwaysRateLimit(): void
{
    $fake = new class extends PrismFake
    {
        public function __construct() {}

        public function structured(StructuredRequest $request): StructuredResponse
        {
            throw PrismRateLimitedException::make();
        }
    };

    app()->instance(PrismManager::class, new class($fake) extends PrismManager
    {
        public function __construct(private readonly PrismFake $fake) {}

        public function resolve(ProviderEnum|string $name, array $providerConfig = []): PrismFake
        {
            return $this->fake;
        }
    });
}

/**
 * Binds a custom PrismManager that throws PrismException on the first call
 * and returns the given response on subsequent calls.
 */
function bindPrismExceptionOnPrimaryThenSuccessOnFallback(StructuredResponse $successResponse): PrismFake
{
    $fake = new class($successResponse) extends PrismFake
    {
        public int $callCount = 0;

        public function __construct(private readonly StructuredResponse $successResponse) {}

        public function structured(StructuredRequest $request): StructuredResponse
        {
            $this->callCount++;

            throw_if($this->callCount === 1, PrismException::class, 'Connection timed out');

            return $this->successResponse;
        }
    };

    app()->instance(PrismManager::class, new class($fake) extends PrismManager
    {
        public function __construct(private readonly PrismFake $fake) {}

        public function resolve(ProviderEnum|string $name, array $providerConfig = []): PrismFake
        {
            return $this->fake;
        }
    });

    return $fake;
}

/** Binds a custom PrismManager that always throws PrismException. */
function bindAlwaysPrismException(): void
{
    $fake = new class extends PrismFake
    {
        public function __construct() {}

        public function structured(StructuredRequest $request): StructuredResponse
        {
            throw new PrismException('Connection timed out');
        }
    };

    app()->instance(PrismManager::class, new class($fake) extends PrismManager
    {
        public function __construct(private readonly PrismFake $fake) {}

        public function resolve(ProviderEnum|string $name, array $providerConfig = []): PrismFake
        {
            return $this->fake;
        }
    });
}

/** Binds a custom PrismManager that always throws PrismRequestTooLargeException. */
function bindAlwaysRequestTooLarge(): void
{
    $fake = new class extends PrismFake
    {
        public function __construct() {}

        public function structured(StructuredRequest $request): StructuredResponse
        {
            throw new PrismRequestTooLargeException('google');
        }
    };

    app()->instance(PrismManager::class, new class($fake) extends PrismManager
    {
        public function __construct(private readonly PrismFake $fake) {}

        public function resolve(ProviderEnum|string $name, array $providerConfig = []): PrismFake
        {
            return $this->fake;
        }
    });
}

/** Binds a custom PrismManager that always throws PrismProviderOverloadedException. */
function bindAlwaysProviderOverloaded(): void
{
    $fake = new class extends PrismFake
    {
        public function __construct() {}

        public function structured(StructuredRequest $request): StructuredResponse
        {
            throw new PrismProviderOverloadedException('Provider overloaded');
        }
    };

    app()->instance(PrismManager::class, new class($fake) extends PrismManager
    {
        public function __construct(private readonly PrismFake $fake) {}

        public function resolve(ProviderEnum|string $name, array $providerConfig = []): PrismFake
        {
            return $this->fake;
        }
    });
}

/** Binds a PrismManager that fails loudly if the provider is reached at all. */
function bindPrismThatMustNotBeCalled(): void
{
    $fake = new class extends PrismFake
    {
        public function __construct() {}

        public function structured(StructuredRequest $request): StructuredResponse
        {
            throw new RuntimeException('Prism was called even though every circuit breaker is open.');
        }
    };

    app()->instance(PrismManager::class, new class($fake) extends PrismManager
    {
        public function __construct(private readonly PrismFake $fake) {}

        public function resolve(ProviderEnum|string $name, array $providerConfig = []): PrismFake
        {
            return $this->fake;
        }
    });
}

it('extracts work experiences and education from a valid CV', function (): void {
    Prism::fake([
        StructuredResponseFake::make()->withStructured(onboardingValidCvStructured()),
    ]);

    $result = resolve(CompleteOnboardingAction::class)->execute(onboardingMakeFakeFile());

    expect($result)->toBeInstanceOf(CandidateOnboardingDTO::class)
        ->and($result->work_experiences)->toHaveCount(1)
        ->and($result->education)->toHaveCount(1);
});

it('completes the analysis when the model answers a date with a placeholder', function (): void {
    $structured = onboardingValidCvStructured();
    $structured['work_experiences'][0]['start_date'] = 'N/A';
    $structured['education'][0]['start_date'] = 'N/A';
    $structured['education'][0]['end_date'] = 'N/A';

    Prism::fake([
        StructuredResponseFake::make()->withStructured($structured),
    ]);

    $result = resolve(CompleteOnboardingAction::class)->execute(onboardingMakeFakeFile());

    expect($result->work_experiences)->toHaveCount(1)
        ->and($result->work_experiences[0]->startDate)->toBeNull()
        ->and($result->work_experiences[0]->companyName)->toBe('3-Pontos')
        ->and($result->education)->toHaveCount(1)
        ->and($result->education[0]->startDate)->toBeNull()
        ->and($result->education[0]->institution)->toBe('FATEC');
});

it('retries with fallback model when primary model hits rate limit', function (): void {
    $successResponse = StructuredResponseFake::make()->withStructured(onboardingValidCvStructured());
    $fake = bindRateLimitOnPrimaryThenSuccessOnFallback($successResponse);

    $result = resolve(CompleteOnboardingAction::class)->execute(onboardingMakeFakeFile());

    expect($result)->toBeInstanceOf(CandidateOnboardingDTO::class)
        ->and($fake->callCount)->toBe(2);
});

it('throws OnboardingException when both primary and fallback models hit rate limit', function (): void {
    bindAlwaysRateLimit();

    expect(fn () => resolve(CompleteOnboardingAction::class)->execute(onboardingMakeFakeFile()))
        ->toThrow(OnboardingException::class);
});

it('throws OnboardingException when the uploaded file is not a CV', function (): void {
    Prism::fake([
        StructuredResponseFake::make()->withStructured([
            'is_cv' => false,
            'rejection_reason' => 'not-an-cv',
            'work_experiences' => [],
            'education' => [],
        ]),
    ]);

    expect(fn () => resolve(CompleteOnboardingAction::class)->execute(onboardingMakeFakeFile()))
        ->toThrow(OnboardingException::class);
});

it('throws OnboardingException when the response omits is_cv', function (): void {
    Prism::fake([
        StructuredResponseFake::make()->withStructured([
            'work_experiences' => [],
            'education' => [],
        ]),
    ]);

    expect(fn () => resolve(CompleteOnboardingAction::class)->execute(onboardingMakeFakeFile()))
        ->toThrow(OnboardingException::class);
});

it('throws OnboardingException when is_cv is false and the rejection reason is unknown', function (): void {
    Prism::fake([
        StructuredResponseFake::make()->withStructured([
            'is_cv' => false,
            'rejection_reason' => 'something the enum does not describe',
            'work_experiences' => [],
            'education' => [],
        ]),
    ]);

    expect(fn () => resolve(CompleteOnboardingAction::class)->execute(onboardingMakeFakeFile()))
        ->toThrow(OnboardingException::class);
});

it('retries with fallback model when primary model throws PrismException', function (): void {
    Cache::flush();

    $successResponse = StructuredResponseFake::make()->withStructured(onboardingValidCvStructured());
    $fake = bindPrismExceptionOnPrimaryThenSuccessOnFallback($successResponse);

    $result = resolve(CompleteOnboardingAction::class)->execute(onboardingMakeFakeFile());

    expect($result)->toBeInstanceOf(CandidateOnboardingDTO::class)
        ->and($fake->callCount)->toBe(2);
});

it('does not open circuit breaker when primary model throws generic PrismException', function (): void {
    Cache::flush();
    $primaryModel = config('ai.provider.gemini.model');

    bindAlwaysPrismException();

    expect(fn () => resolve(CompleteOnboardingAction::class)->execute(onboardingMakeFakeFile()))
        ->toThrow(OnboardingException::class);

    expect(Cache::has('cb:gemini:'.$primaryModel))->toBeFalse();
});

it('opens circuit breaker when primary model throws PrismProviderOverloadedException', function (): void {
    Cache::flush();
    $primaryModel = config('ai.provider.gemini.model');

    bindAlwaysProviderOverloaded();

    expect(fn () => resolve(CompleteOnboardingAction::class)->execute(onboardingMakeFakeFile()))
        ->toThrow(OnboardingException::class);

    expect(Cache::has('cb:gemini:'.$primaryModel))->toBeTrue();
});

it('skips circuit-broken model and goes directly to fallback', function (): void {
    Cache::flush();
    $primaryModel = config('ai.provider.gemini.model');
    $fallbackModel = config('ai.provider.gemini.fallback_model');

    // Pre-activate the circuit breaker for the primary model
    Cache::put('cb:gemini:'.$primaryModel, true, now()->addMinutes(3));

    // With primary CB active, Prism is only called once (for the fallback).
    // If primary were NOT skipped, it would throw PrismException and consume this response,
    // leaving none for the fallback — causing an OnboardingException.
    Prism::fake([
        StructuredResponseFake::make()->withStructured(onboardingValidCvStructured()),
    ]);

    $result = resolve(CompleteOnboardingAction::class)->execute(onboardingMakeFakeFile());

    // Primary CB remains active; fallback CB was never opened (it succeeded)
    expect($result)->toBeInstanceOf(CandidateOnboardingDTO::class)
        ->and(Cache::has('cb:gemini:'.$primaryModel))->toBeTrue()
        ->and(Cache::has('cb:gemini:'.$fallbackModel))->toBeFalse();
});

it('does not open circuit breaker when primary model throws PrismRequestTooLargeException', function (): void {
    Cache::flush();
    $primaryModel = config('ai.provider.gemini.model');

    bindAlwaysRequestTooLarge();

    expect(fn () => resolve(CompleteOnboardingAction::class)->execute(onboardingMakeFakeFile()))
        ->toThrow(OnboardingException::class);

    expect(Cache::has('cb:gemini:'.$primaryModel))->toBeFalse();
});

it('throws ProvidersUnavailableException without calling the provider when every circuit is already open', function (): void {
    Cache::flush();

    Cache::put('cb:gemini:'.config('ai.provider.gemini.model'), true, now()->addMinutes(3));
    Cache::put('cb:gemini:'.config('ai.provider.gemini.fallback_model'), true, now()->addMinutes(3));

    bindPrismThatMustNotBeCalled();

    expect(fn () => resolve(CompleteOnboardingAction::class)->execute(onboardingMakeFakeFile()))
        ->toThrow(ProvidersUnavailableException::class);
});

it('carries the originating provider error when every model opens its circuit in the same run', function (): void {
    Cache::flush();

    bindAlwaysProviderOverloaded();

    $exception = null;

    try {
        resolve(CompleteOnboardingAction::class)->execute(onboardingMakeFakeFile());
    } catch (OnboardingException $onboardingException) {
        $exception = $onboardingException;
    }

    expect($exception)->toBeInstanceOf(ProvidersUnavailableException::class)
        ->and($exception?->getPrevious())->toBeInstanceOf(PrismProviderOverloadedException::class);
});

it('keeps the retryable rate limiting error while at least one circuit stays closed', function (): void {
    Cache::flush();

    bindAlwaysRequestTooLarge();

    $exception = null;

    try {
        resolve(CompleteOnboardingAction::class)->execute(onboardingMakeFakeFile());
    } catch (OnboardingException $onboardingException) {
        $exception = $onboardingException;
    }

    expect($exception)->toBeInstanceOf(OnboardingException::class)
        ->and($exception)->not->toBeInstanceOf(ProvidersUnavailableException::class);
});
