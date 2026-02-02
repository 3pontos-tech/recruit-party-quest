<?php

declare(strict_types=1);

use He4rt\App\Livewire\MyProfile\CandidateWorkExperience;
use He4rt\Candidates\Models\Candidate;
use He4rt\Candidates\Models\WorkExperience;
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
    Livewire::test(CandidateWorkExperience::class)
        ->assertOk()
        ->assertSee(__('panel-app::pages/settings.work_experience.heading'));
});

it('renders with existing work experience data', function (): void {
    WorkExperience::factory()
        ->for($this->candidate, 'candidate')
        ->create([
            'company_name' => 'Acme Corp',
        ]);

    Livewire::test(CandidateWorkExperience::class)
        ->assertOk();

    expect($this->candidate->workExperiences()->count())->toBe(1);
});

it('can submit work experience form', function (): void {
    Livewire::test(CandidateWorkExperience::class)
        ->call('submit')
        ->assertOk();
});
