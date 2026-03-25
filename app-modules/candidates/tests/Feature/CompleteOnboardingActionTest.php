<?php

declare(strict_types=1);

use He4rt\Candidates\Actions\Onboarding\CompleteOnboardingAction;
use He4rt\Candidates\DTOs\CandidateOnboardingDTO;
use He4rt\Candidates\Exceptions\OnboardingException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Prism\Prism\Enums\Provider as ProviderEnum;
use Prism\Prism\Exceptions\PrismRateLimitedException;
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

it('extracts work experiences and education from a valid CV', function (): void {
    Prism::fake([
        StructuredResponseFake::make()->withStructured(onboardingValidCvStructured()),
    ]);

    $result = resolve(CompleteOnboardingAction::class)->execute(onboardingMakeFakeFile());

    expect($result)->toBeInstanceOf(CandidateOnboardingDTO::class)
        ->and($result->work_experiences)->toHaveCount(1)
        ->and($result->education)->toHaveCount(1);
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
