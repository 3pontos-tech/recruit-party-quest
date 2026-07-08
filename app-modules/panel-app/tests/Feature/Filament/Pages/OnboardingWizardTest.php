<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\App\Filament\Pages\OnboardingWizard;
use He4rt\Candidates\Models\Candidate;
use He4rt\Recruitment\Requisitions\Enums\ExperienceLevelEnum;
use He4rt\Users\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->candidate = Candidate::factory()->create([
        'user_id' => $this->user->id,
        'is_onboarded' => false,
    ]);

    actingAs($this->user);
    filament()->setCurrentPanel(FilamentPanel::App->value);

    Storage::fake('public');
    Event::fake();
    Queue::fake();
});

describe('Page Rendering & Access', function (): void {
    it('should render onboarding page successfully', function (): void {
        livewire(OnboardingWizard::class)
            ->assertOk()
            ->assertSee('onboarding');
    });

    it('should display correct page title and heading', function (): void {
        $component = livewire(OnboardingWizard::class);

        expect($component->instance()->getTitle())
            ->toBe(__('panel-app::pages/onboarding.title'));
    });

    it('should redirect to dashboard if user already completed onboarding', function (): void {
        $onboardedUser = User::factory()->create();
        Candidate::factory()->create([
            'user_id' => $onboardedUser->id,
            'is_onboarded' => true,
            'onboarding_completed_at' => now(),
        ]);

        actingAs($onboardedUser);

        livewire(OnboardingWizard::class)
            ->assertRedirect(route('filament.app.pages.dashboard'));
    });
});

describe('Initial State & Configuration', function (): void {
    it('should initialize with correct default values', function (): void {
        $component = livewire(OnboardingWizard::class);
        $data = $component->getData();

        expect($data['wizardVisible'])->toBeFalse()
            ->and($data['canSkipResumeAnalysis'])->toBeTrue()
            ->and($data['data'])->toBeArray();
    });

    it('should hide wizard initially and show resume upload', function (): void {
        livewire(OnboardingWizard::class)
            ->assertOk()
            ->assertSet('wizardVisible', false)
            ->assertSee('cv_file')
            ->assertSee(__('panel-app::pages/onboarding.actions.continue_without_upload'));
    });

    it('should configure wizard with correct step count', function (): void {
        $component = livewire(OnboardingWizard::class);
        $steps = $component->instance()->getSteps();

        expect($steps)->toHaveCount(4)
            ->and($steps[0]['id'])->toBe('account')
            ->and($steps[1]['id'])->toBe('profile')
            ->and($steps[2]['id'])->toBe('preferences')
            ->and($steps[3]['id'])->toBe('review');
    });
});

it('should preserve form state across interactions', function (): void {
    $component = livewire(OnboardingWizard::class)
        ->set('data.timezone', 'America/New_York')
        ->set('data.preferred_language', 'en_US');

    expect($component->get('data.timezone'))
        ->toBe('America/New_York')
        ->and($component->get('data.preferred_language'))
        ->toBe('en_US');
});

it('should display appropriate error messages for failures', function (): void {
    livewire(OnboardingWizard::class)
        ->set('wizardVisible', true)
        ->set('data.data_consent_given', false)
        ->call('handleRegistration')
        ->assertHasFormErrors(['data_consent_given'], 'content');
});

describe('Resume Analysis Error Handling', function (): void {
    it('sends a rate limit notification when code is 503', function (): void {
        livewire(OnboardingWizard::class)
            ->call('again', ['message' => 'Rate limit exceeded', 'code' => 503])
            ->assertNotified(__('panel-app::pages/onboarding.notifications.rate_limit.title'));
    });

    it('sends an invalid file notification when code is 422', function (): void {
        livewire(OnboardingWizard::class)
            ->call('again', ['message' => 'File sent is not a curriculum.', 'code' => 422])
            ->assertNotified(__('panel-app::pages/onboarding.notifications.is_not_cv.title'));
    });

    it('falls back to rate limit notification when code is absent', function (): void {
        livewire(OnboardingWizard::class)
            ->call('again', ['message' => 'Unknown error'])
            ->assertNotified(__('panel-app::pages/onboarding.notifications.rate_limit.title'));
    });

    it('re-enables the skip button after a resume analysis error', function (): void {
        livewire(OnboardingWizard::class)
            ->set('canSkipResumeAnalysis', false)
            ->call('again', ['message' => 'error', 'code' => 503])
            ->assertSet('canSkipResumeAnalysis', true);
    });
});

describe('Resume Analysis Completion (broadcast payload)', function (): void {
    // The .finished event arrives through Echo: the AnalyzeResumeEvent DTOs become
    // raw JSON arrays on the browser round-trip before reaching Livewire again.
    it('shows the wizard with prefilled state when the finished event arrives as raw arrays', function (): void {
        $component = livewire(OnboardingWizard::class)
            ->call('onResumeAnalyzed', [
                'status' => 'finished',
                'userId' => $this->user->id,
                'fields' => [
                    'education' => [
                        [
                            'institution' => 'FATEC',
                            'degree' => 'Bacharelado',
                            'field_of_study' => 'ADS',
                            'is_enrolled' => true,
                            'start_date' => '2021-02-01',
                            'end_date' => null,
                        ],
                    ],
                    'work_experiences' => [
                        [
                            'company_name' => '3-Pontos',
                            'description' => 'PHP Developer',
                            'is_currently_working_here' => true,
                            'start_date' => '2023-06-15',
                            'end_date' => null,
                        ],
                    ],
                ],
            ])
            ->assertSet('wizardVisible', true)
            ->assertSet('canSkipResumeAnalysis', true)
            ->assertNotified('Currículo carregado com sucesso!');

        $workExperiences = array_values($component->get('data.work_experiences'));
        $education = array_values($component->get('data.education'));

        expect($workExperiences)->toHaveCount(1)
            ->and($workExperiences[0]['company_name'])->toBe('3-Pontos')
            ->and($workExperiences[0]['is_currently_working_here'])->toBeTrue()
            ->and($education)->toHaveCount(1)
            ->and($education[0]['institution'])->toBe('FATEC');
    });

    it('handles a finished event without education or work experiences', function (): void {
        livewire(OnboardingWizard::class)
            ->call('onResumeAnalyzed', [
                'status' => 'finished',
                'userId' => $this->user->id,
                'fields' => [
                    'education' => [],
                    'work_experiences' => [],
                ],
            ])
            ->assertSet('wizardVisible', true)
            ->assertSet('data.work_experiences', [])
            ->assertSet('data.education', []);
    });
});

describe('Finalization validation (issue #166)', function (): void {
    it('blocks finalization when phone is invalid for BR and does not persist', function (): void {
        livewire(OnboardingWizard::class)
            ->set('wizardVisible', true)
            ->set('data.timezone', 'America/Sao_Paulo')
            ->set('data.preferred_language', 'pt_BR')
            ->set('data.phone', '+551198765432100')
            ->set('data.data_consent_given', true)
            ->set('data.expected_salary', '50000')
            ->set('data.expected_salary_currency', 'BRL')
            ->set('data.availability_date', now()->addDays(30)->format('Y-m-d'))
            ->set('data.experience_level', ExperienceLevelEnum::Junior->value)
            ->set('data.confirm_submission', true)
            ->set('data.work_experiences', [])
            ->set('data.education', [])
            ->call('handleRegistration')
            ->assertHasFormErrors(['phone'], 'content')
            ->assertNoRedirect();

        expect($this->candidate->fresh()->is_onboarded)->toBeFalse();
    });

    it('blocks finalization when a work experience is missing company_name without TypeError', function (): void {
        livewire(OnboardingWizard::class)
            ->set('wizardVisible', true)
            ->set('data.timezone', 'America/Sao_Paulo')
            ->set('data.preferred_language', 'pt_BR')
            ->set('data.phone', '+5511987654321')
            ->set('data.data_consent_given', true)
            ->set('data.expected_salary', '50000')
            ->set('data.expected_salary_currency', 'BRL')
            ->set('data.availability_date', now()->addDays(30)->format('Y-m-d'))
            ->set('data.experience_level', ExperienceLevelEnum::Junior->value)
            ->set('data.confirm_submission', true)
            ->set('data.work_experiences', [
                'item-1' => [
                    'company_name' => null,
                    'description' => 'Some description',
                    'start_date' => '2020-01-01',
                    'end_date' => null,
                    'is_currently_working_here' => true,
                ],
            ])
            ->set('data.education', [])
            ->call('handleRegistration')
            ->assertHasFormErrors(['work_experiences.item-1.company_name'], 'content')
            ->assertNoRedirect();

        expect($this->candidate->fresh()->is_onboarded)->toBeFalse();
    });

    it('blocks finalization when data consent is not given', function (): void {
        livewire(OnboardingWizard::class)
            ->set('wizardVisible', true)
            ->set('data.timezone', 'America/Sao_Paulo')
            ->set('data.preferred_language', 'pt_BR')
            ->set('data.phone', '+5511987654321')
            ->set('data.data_consent_given', false)
            ->set('data.expected_salary', '50000')
            ->set('data.expected_salary_currency', 'BRL')
            ->set('data.availability_date', now()->addDays(30)->format('Y-m-d'))
            ->set('data.experience_level', ExperienceLevelEnum::Junior->value)
            ->set('data.confirm_submission', true)
            ->set('data.work_experiences', [])
            ->set('data.education', [])
            ->call('handleRegistration')
            ->assertHasFormErrors(['data_consent_given'], 'content')
            ->assertNoRedirect();

        expect($this->candidate->fresh()->is_onboarded)->toBeFalse();
    });
});

describe('Complete Registration Flow', function (): void {
    it('redirects to the stored intent after completing onboarding', function (): void {
        $intentUrl = url('/vagas/alguma-vaga/candidatar');
        session(['url.intended' => $intentUrl]);

        livewire(OnboardingWizard::class)
            ->set('wizardVisible', true)
            ->set('data.expected_salary', '75000')
            ->set('data.expected_salary_currency', 'USD')
            ->set('data.availability_date', now()->addDays(30)->format('Y-m-d'))
            ->set('data.willing_to_relocate', true)
            ->set('data.is_open_to_remote', true)
            ->set('data.experience_level', ExperienceLevelEnum::MidLevel->value)
            ->set('data.timezone', 'America/New_York')
            ->set('data.preferred_language', 'en_US')
            ->set('data.phone', '+5511987654321')
            ->set('data.confirm_submission', true)
            ->set('data.data_consent_given', true)
            ->set('data.work_experiences', [])
            ->set('data.education', [])
            ->call('handleRegistration')
            ->assertHasNoFormErrors()
            ->assertRedirect($intentUrl);
    });

    it('should complete full onboarding successfully', function (): void {
        livewire(OnboardingWizard::class)
            ->set('wizardVisible', true)
            ->set('data.expected_salary', '75000')
            ->set('data.expected_salary_currency', 'USD')
            ->set('data.availability_date', now()->addDays(30)->format('Y-m-d'))
            ->set('data.willing_to_relocate', true)
            ->set('data.is_open_to_remote', true)
            ->set('data.experience_level', ExperienceLevelEnum::MidLevel->value)
            ->set('data.timezone', 'America/New_York')
            ->set('data.preferred_language', 'en_US')
            ->set('data.phone', '+5511987654321')
            ->set('data.confirm_submission', true)
            ->set('data.data_consent_given', true)
            ->set('data.work_experiences', [])
            ->set('data.education', [])
            ->call('handleRegistration')
            ->assertOk()
            ->assertHasNoFormErrors()
            ->assertRedirectToRoute('filament.app.pages.dashboard');

        assertDatabaseHas(Candidate::class, [
            'user_id' => $this->user->id,
            'experience_level' => ExperienceLevelEnum::MidLevel->value,
            'expected_salary' => 75000,
            'is_open_to_remote' => 1,
            'willing_to_relocate' => 1,
            'phone_number' => '+5511987654321',
            'is_onboarded' => true,
        ]);
    });

    it('should persist all candidate data to database', function (): void {
        $testData = [
            'expected_salary' => '85000',
            'expected_salary_currency' => 'EUR',
            'availability_date' => now()->addDays(60)->format('Y-m-d'),
            'willing_to_relocate' => false,
            'is_open_to_remote' => true,
            'experience_level' => 'senior',
            'timezone' => 'Europe/London',
            'preferred_language' => 'en_US',
            'phone' => '+5511912345678',
            'confirm_submission' => true,
            'data_consent_given' => true,
            'work_experiences' => [],
            'education' => [],
        ];

        livewire(OnboardingWizard::class)
            ->set('wizardVisible', true)
            ->set('data', $testData)
            ->call('handleRegistration');

        assertDatabaseHas(Candidate::class, [
            'user_id' => $this->user->id,
            'expected_salary' => 85000,
            'expected_salary_currency' => 'EUR',
            'experience_level' => 'senior',
            'timezone' => 'Europe/London',
            'preferred_language' => 'en_US',
            'phone_number' => '+5511912345678',
            'is_onboarded' => true,
        ]);
    });

    it('should save phone number from onboarding data', function (): void {
        livewire(OnboardingWizard::class)
            ->set('wizardVisible', true)
            ->set('data.expected_salary', '50000')
            ->set('data.expected_salary_currency', 'BRL')
            ->set('data.availability_date', now()->addDays(30)->format('Y-m-d'))
            ->set('data.experience_level', 'junior')
            ->set('data.timezone', 'America/Sao_Paulo')
            ->set('data.preferred_language', 'pt_BR')
            ->set('data.phone', '+5511987654321')
            ->set('data.confirm_submission', true)
            ->set('data.data_consent_given', true)
            ->set('data.work_experiences', [])
            ->set('data.education', [])
            ->call('handleRegistration')
            ->assertHasNoFormErrors()
            ->assertRedirectToRoute('filament.app.pages.dashboard');

        assertDatabaseHas(Candidate::class, [
            'user_id' => $this->user->id,
            'phone_number' => '+5511987654321',
        ]);
    });

    it('accepts an international phone number from another country (UY)', function (): void {
        livewire(OnboardingWizard::class)
            ->set('wizardVisible', true)
            ->set('data.expected_salary', '50000')
            ->set('data.expected_salary_currency', 'BRL')
            ->set('data.availability_date', now()->addDays(30)->format('Y-m-d'))
            ->set('data.experience_level', 'junior')
            ->set('data.timezone', 'America/Montevideo')
            ->set('data.preferred_language', 'pt_BR')
            ->set('data.phone', '+59891234567')
            ->set('data.confirm_submission', true)
            ->set('data.data_consent_given', true)
            ->set('data.work_experiences', [])
            ->set('data.education', [])
            ->call('handleRegistration')
            ->assertHasNoFormErrors()
            ->assertRedirectToRoute('filament.app.pages.dashboard');

        assertDatabaseHas(Candidate::class, [
            'user_id' => $this->user->id,
            'phone_number' => '+59891234567',
        ]);
    });

    it('should mark candidate as onboarded with timestamp', function (): void {
        livewire(OnboardingWizard::class)
            ->set('wizardVisible', true)
            ->set('data.expected_salary', '50000')
            ->set('data.expected_salary_currency', 'USD')
            ->set('data.availability_date', now()->addMonth()->format('Y-m-d'))
            ->set('data.experience_level', 'junior')
            ->set('data.timezone', 'UTC')
            ->set('data.preferred_language', 'en_US')
            ->set('data.phone', '+5511987654321')
            ->set('data.confirm_submission', true)
            ->set('data.data_consent_given', true)
            ->set('data.work_experiences', [])
            ->set('data.education', [])
            ->call('handleRegistration');

        $candidate = $this->candidate->fresh();
        expect($candidate->is_onboarded)->toBeTrue()
            ->and($candidate->onboarding_completed_at)->not->toBeNull();
    });

    it('should handle registration without data consent', function (): void {
        livewire(OnboardingWizard::class)
            ->set('wizardVisible', true)
            ->set('data.data_consent_given', false)
            ->call('handleRegistration')
            ->assertHasFormErrors(['data_consent_given'], 'content');

        $candidate = $this->candidate->fresh();
        expect($candidate->is_onboarded)->toBeFalse();
    });
});
