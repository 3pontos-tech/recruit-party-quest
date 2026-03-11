<?php

declare(strict_types=1);

use He4rt\App\Livewire\ProfileCard;
use He4rt\Candidates\Database\Factories\CandidateFactory;
use He4rt\Candidates\Models\Education;
use He4rt\Candidates\Models\Skill;
use He4rt\Candidates\Models\WorkExperience;
use He4rt\Links\Link;
use He4rt\Links\LinkTypeEnum;
use He4rt\Users\Database\Factories\UserFactory;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->user = UserFactory::new()->create();
    $this->candidate = CandidateFactory::new()->create([
        'user_id' => $this->user->id,
    ]);

    actingAs($this->user);
});

it('displays profile card with completion percentage', function (): void {
    livewire(ProfileCard::class)
        ->assertSuccessful()
        ->assertViewHas('profileCompletionPercentage')
        ->assertSee(__('panel-app::livewire/profile-card.progress.title'))
        ->assertSee('%');
});

it('calculates 0% completion for minimal profile', function (): void {
    $user = UserFactory::new()->create();
    $candidate = CandidateFactory::new()->create([
        'user_id' => $user->id,
        'phone_number' => null,
        'headline' => null,
        'summary' => null,
        'experience_level' => null,
        'expected_salary' => null,
        'availability_date' => null,
    ]);

    actingAs($user);

    livewire(ProfileCard::class)
        ->assertSet('profileCompletionPercentage', fn ($value) => $value < 50);
});

it('calculates higher completion with basic info filled', function (): void {
    $this->candidate->update([
        'phone_number' => '+55 11 98765-4321',
        'headline' => 'Senior Developer',
        'summary' => 'Experienced developer with 10+ years',
        'experience_level' => 'senior',
    ]);

    $percentage = $this->candidate->fresh()->profile_completion_percentage;

    expect($percentage)->toBeGreaterThan(0);
});

it('calculates higher completion with work experience', function (): void {
    $this->candidate->update([
        'phone_number' => '+55 11 98765-4321',
        'headline' => 'Senior Developer',
        'summary' => 'Experienced developer',
        'experience_level' => 'senior',
    ]);

    WorkExperience::factory()->create([
        'candidate_id' => $this->candidate->id,
    ]);

    $percentage = $this->candidate->fresh()->profile_completion_percentage;

    expect($percentage)->toBeGreaterThan(40);
});

it('calculates higher completion with education', function (): void {
    $this->candidate->update([
        'phone_number' => '+55 11 98765-4321',
        'headline' => 'Senior Developer',
        'summary' => 'Experienced developer',
        'experience_level' => 'senior',
    ]);

    WorkExperience::factory()->create([
        'candidate_id' => $this->candidate->id,
    ]);

    Education::factory()->create([
        'candidate_id' => $this->candidate->id,
    ]);

    $percentage = $this->candidate->fresh()->profile_completion_percentage;

    expect($percentage)->toBeGreaterThan(55);
});

it('calculates higher completion with skills', function (): void {
    $this->candidate->update([
        'phone_number' => '+55 11 98765-4321',
        'headline' => 'Senior Developer',
        'summary' => 'Experienced developer',
        'experience_level' => 'senior',
    ]);

    WorkExperience::factory()->create([
        'candidate_id' => $this->candidate->id,
    ]);

    Education::factory()->create([
        'candidate_id' => $this->candidate->id,
    ]);

    $skills = Skill::factory()->count(3)->create();
    foreach ($skills as $skill) {
        $this->candidate->skills()->attach($skill->id, [
            'years_of_experience' => 2,
            'proficiency_level' => 3,
        ]);
    }

    $percentage = $this->candidate->fresh()->profile_completion_percentage;

    expect($percentage)->toBeGreaterThan(70);
});

it('calculates higher completion with preferences', function (): void {
    $this->candidate->update([
        'phone_number' => '+55 11 98765-4321',
        'headline' => 'Senior Developer',
        'summary' => 'Experienced developer',
        'experience_level' => 'senior',
        'expected_salary' => 10000.00,
        'availability_date' => now()->addMonth(),
        'is_open_to_remote' => true,
    ]);

    WorkExperience::factory()->create([
        'candidate_id' => $this->candidate->id,
    ]);

    Education::factory()->create([
        'candidate_id' => $this->candidate->id,
    ]);

    $skills = Skill::factory()->count(3)->create();
    foreach ($skills as $skill) {
        $this->candidate->skills()->attach($skill->id, [
            'years_of_experience' => 2,
            'proficiency_level' => 3,
        ]);
    }

    $percentage = $this->candidate->fresh()->profile_completion_percentage;

    expect($percentage)->toBeGreaterThan(85);
});

it('calculates 100% completion with full profile', function (): void {
    $this->candidate->update([
        'phone_number' => '+55 11 98765-4321',
        'headline' => 'Senior Developer',
        'summary' => 'Experienced developer with 10+ years',
        'experience_level' => 'senior',
        'expected_salary' => 10000.00,
        'availability_date' => now()->addMonth(),
        'is_open_to_remote' => true,
        'willing_to_relocate' => false,
    ]);

    WorkExperience::factory()->create([
        'candidate_id' => $this->candidate->id,
    ]);

    Education::factory()->create([
        'candidate_id' => $this->candidate->id,
    ]);

    $skills = Skill::factory()->count(3)->create();
    foreach ($skills as $skill) {
        $this->candidate->skills()->attach($skill->id, [
            'years_of_experience' => 2,
            'proficiency_level' => 3,
        ]);
    }

    $links = Link::factory()->count(2)->create([
        'type' => LinkTypeEnum::LinkedIn,
    ]);
    foreach ($links as $link) {
        $this->user->links()->attach($link);
    }

    $percentage = $this->candidate->fresh()->profile_completion_percentage;

    expect($percentage)->toBe(100);
});

it('renders profile completion section in view', function (): void {
    livewire(ProfileCard::class)
        ->assertSee('%')
        ->assertSee(__('panel-app::livewire/profile-card.progress.title'))
        ->assertSee(__('panel-app::livewire/profile-card.progress.description'));
});

it('displays missing sections when profile is incomplete', function (): void {
    $missingSections = $this->candidate->getMissingProfileSections();

    // Candidate from beforeEach will have some missing sections
    expect($missingSections)->toBeArray();
});

it('does not display missing sections when profile is complete', function (): void {
    $this->candidate->update([
        'phone_number' => '+55 11 98765-4321',
        'headline' => 'Senior Developer',
        'summary' => 'Experienced developer with 10+ years',
        'experience_level' => 'senior',
        'expected_salary' => 10000.00,
        'availability_date' => now()->addMonth(),
        'is_open_to_remote' => true,
        'willing_to_relocate' => false,
    ]);

    WorkExperience::factory()->create([
        'candidate_id' => $this->candidate->id,
    ]);

    Education::factory()->create([
        'candidate_id' => $this->candidate->id,
    ]);

    $skills = Skill::factory()->count(3)->create();
    foreach ($skills as $skill) {
        $this->candidate->skills()->attach($skill->id, [
            'years_of_experience' => 2,
            'proficiency_level' => 3,
        ]);
    }

    $links = Link::factory()->count(2)->create([
        'type' => LinkTypeEnum::LinkedIn,
    ]);
    foreach ($links as $link) {
        $this->user->links()->attach($link);
    }

    livewire(ProfileCard::class)
        ->assertDontSee(__('panel-app::livewire/profile-card.progress.missing_sections'));
});

it('returns correct missing sections from candidate model', function (): void {
    $this->candidate->update([
        'phone_number' => null,
        'headline' => null,
    ]);

    $missingSections = $this->candidate->getMissingProfileSections();

    expect($missingSections)->toHaveKey('basic_info')
        ->and($missingSections['basic_info'])->toHaveKey('label', 'basic_info');
});
