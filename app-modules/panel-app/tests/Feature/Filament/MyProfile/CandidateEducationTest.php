<?php

declare(strict_types=1);

use He4rt\App\Livewire\MyProfile\CandidateEducation;
use He4rt\Candidates\Models\Candidate;
use He4rt\Candidates\Models\Education;
use He4rt\Users\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->candidate = Candidate::factory()->for($this->user, 'user')->create();

    $this->user->refresh();

    actingAs($this->user);
});

it('renders the component successfully', function (): void {
    Livewire::test(CandidateEducation::class)
        ->assertOk()
        ->assertSee(__('panel-app::pages/settings.education.heading'));
});

it('renders with existing education data', function (): void {
    Education::factory()
        ->for($this->candidate, 'candidate')
        ->create([
            'institution' => 'MIT',
            'degree' => 'Bachelor',
            'field_of_study' => 'Computer Science',
        ]);

    Livewire::test(CandidateEducation::class)
        ->assertOk();

    expect($this->candidate->degrees()->count())->toBe(1);
});

it('can submit education form', function (): void {
    Livewire::test(CandidateEducation::class)
        ->call('submit')
        ->assertOk();
});
