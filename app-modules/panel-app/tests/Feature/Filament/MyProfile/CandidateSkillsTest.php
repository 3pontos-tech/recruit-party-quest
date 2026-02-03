<?php

declare(strict_types=1);

use He4rt\App\Livewire\MyProfile\CandidateSkills;
use He4rt\Candidates\Models\Candidate;
use He4rt\Candidates\Models\CandidateSkill;
use He4rt\Candidates\Models\Skill;
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
    Livewire::test(CandidateSkills::class)
        ->assertOk()
        ->assertSee(__('panel-app::pages/settings.skills.heading'));
});

it('renders with existing skills data', function (): void {
    $skill = Skill::factory()->create(['name' => 'PHP']);

    CandidateSkill::factory()->create([
        'candidate_id' => $this->candidate->id,
        'skill_id' => $skill->id,
        'years_of_experience' => 5,
        'proficiency_level' => 4,
    ]);

    Livewire::test(CandidateSkills::class)
        ->assertOk();

    expect($this->candidate->skills()->count())->toBe(1);
});

it('can submit skills form', function (): void {
    Livewire::test(CandidateSkills::class)
        ->call('submit')
        ->assertOk();
});
