<?php

declare(strict_types=1);

use He4rt\App\Livewire\MyProfile\CandidateProfileInfo;
use He4rt\Candidates\Models\Candidate;
use He4rt\Users\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->candidate = Candidate::factory()->for($this->user, 'user')->create([
        'headline' => 'Test Headline',
        'summary' => 'Test Summary',
        'phone_number' => '+5511999999999',
    ]);

    $this->user->refresh();

    actingAs($this->user);
});

it('renders the component successfully', function (): void {
    Livewire::test(CandidateProfileInfo::class)
        ->assertOk()
        ->assertSee(__('panel-app::pages/settings.profile_info.heading'));
});

it('displays form fields', function (): void {
    Livewire::test(CandidateProfileInfo::class)
        ->assertFormFieldExists('headline')
        ->assertFormFieldExists('summary')
        ->assertFormFieldExists('phone_number');
});

it('can call submit without errors', function (): void {
    Livewire::test(CandidateProfileInfo::class)
        ->call('submit')
        ->assertHasNoFormErrors();
});
