<?php

declare(strict_types=1);

use He4rt\App\Livewire\MyProfile\CandidatePreferences;
use He4rt\Candidates\Models\Candidate;
use He4rt\Users\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->candidate = Candidate::factory()->for($this->user, 'user')->create([
        'expected_salary' => 80000.00,
        'expected_salary_currency' => 'USD',
        'experience_level' => 'senior',
        'timezone' => 'UTC',
        'preferred_language' => 'en_US',
        'willing_to_relocate' => false,
        'is_open_to_remote' => true,
    ]);

    $this->user->refresh();

    actingAs($this->user);
});

it('renders the component successfully', function (): void {
    Livewire::test(CandidatePreferences::class)
        ->assertOk()
        ->assertSee(__('panel-app::pages/settings.preferences.heading'));
});

it('displays form fields', function (): void {
    Livewire::test(CandidatePreferences::class)
        ->assertFormFieldExists('expected_salary')
        ->assertFormFieldExists('expected_salary_currency')
        ->assertFormFieldExists('availability_date')
        ->assertFormFieldExists('willing_to_relocate')
        ->assertFormFieldExists('is_open_to_remote')
        ->assertFormFieldExists('experience_level')
        ->assertFormFieldExists('timezone')
        ->assertFormFieldExists('preferred_language');
});

it('can call submit without errors', function (): void {
    Livewire::test(CandidatePreferences::class)
        ->call('submit')
        ->assertOk();
});
