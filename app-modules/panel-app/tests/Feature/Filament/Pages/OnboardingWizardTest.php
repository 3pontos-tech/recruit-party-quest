<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\App\Filament\Pages\OnboardingWizard;
use He4rt\Candidates\Models\Candidate;
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
        ->set('data.data_consent_given', false)
        ->call('handleRegistration')
        ->assertNotified(__('panel-app::pages/onboarding.notifications.consent_required.title'));
});

describe('Complete Registration Flow', function (): void {
    it('should complete full onboarding successfully', function (): void {
        livewire(OnboardingWizard::class)
            ->set('data.expected_salary', '75000')
            ->set('data.expected_salary_currency', 'USD')
            ->set('data.availability_date', now()->addDays(30)->format('Y-m-d'))
            ->set('data.willing_to_relocate', true)
            ->set('data.is_open_to_remote', true)
            ->set('data.experience_level', 'mid')
            ->set('data.timezone', 'America/New_York')
            ->set('data.preferred_language', 'en_US')
            ->set('data.confirm_submission', true)
            ->set('data.data_consent_given', true)
            ->set('data.work_experiences', [])
            ->set('data.education', [])
            ->call('handleRegistration')
            ->assertOk()
            ->assertHasNoFormErrors()
            ->assertRedirectToRoute('filament.app.pages.app-dashboard');

        assertDatabaseHas(Candidate::class, [
            'user_id' => $this->user->id,
            'experience_level' => 'mid',
            'expected_salary' => 75000,
            'is_open_to_remote' => 1,
            'willing_to_relocate' => 1,
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
            'confirm_submission' => true,
            'data_consent_given' => true,
            'work_experiences' => [],
            'education' => [],
        ];

        livewire(OnboardingWizard::class)
            ->set('data', $testData)
            ->call('handleRegistration');

        assertDatabaseHas(Candidate::class, [
            'user_id' => $this->user->id,
            'expected_salary' => 85000,
            'expected_salary_currency' => 'EUR',
            'experience_level' => 'senior',
            'timezone' => 'Europe/London',
            'preferred_language' => 'en_US',
            'is_onboarded' => true,
        ]);
    });

    it('should mark candidate as onboarded with timestamp', function (): void {
        livewire(OnboardingWizard::class)
            ->set('data.expected_salary', '50000')
            ->set('data.expected_salary_currency', 'USD')
            ->set('data.availability_date', now()->addMonth()->format('Y-m-d'))
            ->set('data.experience_level', 'junior')
            ->set('data.timezone', 'UTC')
            ->set('data.preferred_language', 'en_US')
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
            ->set('data.data_consent_given', false)
            ->call('handleRegistration')
            ->assertNotified(__('panel-app::pages/onboarding.notifications.consent_required.title'));

        $candidate = $this->candidate->fresh();
        expect($candidate->is_onboarded)->toBeFalse();
    });
});
