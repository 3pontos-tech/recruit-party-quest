<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use He4rt\App\Livewire\MyProfile\CandidateResumeUpload;
use He4rt\Candidates\Models\Candidate;
use He4rt\Candidates\Models\Education;
use He4rt\Candidates\Models\WorkExperience;
use He4rt\Users\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->candidate = Candidate::factory()->for($this->user, 'user')->create();
    $this->user->refresh();
    actingAs($this->user);
    filament()->setCurrentPanel(FilamentPanel::App->value);
});

it('renders the component successfully', function (): void {
    Livewire::test(CandidateResumeUpload::class)
        ->assertOk();
});

it('displays upload button when candidate is not on cooldown', function (): void {
    Livewire::test(CandidateResumeUpload::class)
        ->assertOk()
        ->assertSee(__('panel-app::pages/settings.resume_upload.upload_button'));
});

it('finished() persists work_experiences in the database', function (): void {
    $payload = [
        'fields' => [
            'work_experiences' => [
                [
                    'company_name' => '3-Pontos',
                    'description' => 'PHP Developer',
                    'is_currently_working_here' => true,
                    'start_date' => '2024-01-01',
                    'end_date' => null,
                ],
            ],
            'education' => [],
        ],
    ];

    Livewire::test(CandidateResumeUpload::class)
        ->call('finished', $payload)
        ->assertOk();

    assertDatabaseCount(WorkExperience::class, 1);
    assertDatabaseHas(WorkExperience::class, [
        'company_name' => '3-Pontos',
        'description' => 'PHP Developer',
        'is_currently_working_here' => true,
    ]);
});

it('finished() persists education in the database', function (): void {
    $payload = [
        'fields' => [
            'work_experiences' => [],
            'education' => [
                [
                    'institution' => 'MIT',
                    'degree' => 'Bachelor',
                    'field_of_study' => 'Computer Science',
                    'is_enrolled' => false,
                    'start_date' => '2020-01-01',
                    'end_date' => '2024-01-01',
                ],
            ],
        ],
    ];

    Livewire::test(CandidateResumeUpload::class)
        ->call('finished', $payload)
        ->assertOk();

    assertDatabaseCount(Education::class, 1);
    assertDatabaseHas(Education::class, [
        'institution' => 'MIT',
        'degree' => 'Bachelor',
        'field_of_study' => 'Computer Science',
        'is_enrolled' => false,
    ]);
});

it('finished() does not duplicate work_experiences when called with same data twice', function (): void {
    $payload = [
        'fields' => [
            'work_experiences' => [
                [
                    'company_name' => 'Google',
                    'description' => 'Software Engineer',
                    'is_currently_working_here' => false,
                    'start_date' => '2020-01-01',
                    'end_date' => '2023-01-01',
                ],
            ],
            'education' => [],
        ],
    ];

    Livewire::test(CandidateResumeUpload::class)
        ->call('finished', $payload)
        ->assertOk();

    assertDatabaseCount(WorkExperience::class, 1);

    // Call again with same data
    Livewire::test(CandidateResumeUpload::class)
        ->call('finished', $payload)
        ->assertOk();

    assertDatabaseCount(WorkExperience::class, 1);
});

it('finished() does not duplicate education when called with same data twice', function (): void {
    $payload = [
        'fields' => [
            'work_experiences' => [],
            'education' => [
                [
                    'institution' => 'FATEC',
                    'degree' => 'Bacharelado',
                    'field_of_study' => 'ADS',
                    'is_enrolled' => true,
                    'start_date' => '2022-01-01',
                    'end_date' => null,
                ],
            ],
        ],
    ];

    Livewire::test(CandidateResumeUpload::class)
        ->call('finished', $payload)
        ->assertOk();

    assertDatabaseCount(Education::class, 1);

    // Call again with same data
    Livewire::test(CandidateResumeUpload::class)
        ->call('finished', $payload)
        ->assertOk();

    assertDatabaseCount(Education::class, 1);
});

it('finished() sets isOnCooldown and cooldownDaysRemaining after processing', function (): void {
    $payload = [
        'fields' => [
            'work_experiences' => [],
            'education' => [],
        ],
    ];

    $component = Livewire::test(CandidateResumeUpload::class)
        ->call('finished', $payload);

    // After finished(), component should reflect cooldown state
    expect($component->get('isOnCooldown'))->toBeTrue();
    expect($component->get('cooldownDaysRemaining'))->toBe(3);
});

it('finished() redirects to the current page', function (): void {
    $payload = [
        'fields' => [
            'work_experiences' => [],
            'education' => [],
        ],
    ];

    Livewire::test(CandidateResumeUpload::class)
        ->call('finished', $payload)
        ->assertRedirect(url()->current());
});

it('error() sends danger notification with message from payload', function (): void {
    $payload = [
        'message' => 'Something went wrong processing the CV.',
    ];

    Livewire::test(CandidateResumeUpload::class)
        ->call('error', $payload)
        ->assertNotified('Something went wrong processing the CV.');
});
