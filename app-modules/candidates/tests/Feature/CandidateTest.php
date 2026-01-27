<?php

declare(strict_types=1);

use He4rt\Candidates\Models\Candidate;
use He4rt\Candidates\Models\CandidateSkill;
use He4rt\Candidates\Models\Education;
use He4rt\Candidates\Models\Skill;
use He4rt\Candidates\Models\WorkExperience;
use He4rt\Users\User;

it('can create a candidate', function (): void {
    $candidate = Candidate::factory()->create();

    expect($candidate)->toBeInstanceOf(Candidate::class)
        ->and($candidate->id)->not->toBeNull()
        ->and($candidate->user_id)->not->toBeNull();
});

it('belongs to a user', function (): void {
    $user = User::factory()->create();
    $candidate = Candidate::factory()->create(['user_id' => $user->id]);

    expect($candidate->user)->toBeInstanceOf(User::class)
        ->and($candidate->user->id)->toBe($user->id);
});

it('has many education records', function (): void {
    $candidate = Candidate::factory()->create();
    Education::factory()->count(3)->create(['candidate_id' => $candidate->id]);

    expect($candidate->degrees)->toHaveCount(3);
});

it('has many work experience records', function (): void {
    $candidate = Candidate::factory()->create();
    WorkExperience::factory()->count(2)->create(['candidate_id' => $candidate->id]);

    expect($candidate->workExperiences)->toHaveCount(2);
});

it('has many skills through candidate skills', function (): void {
    $candidate = Candidate::factory()->create();
    $skills = Skill::factory()->count(3)->create();

    foreach ($skills as $skill) {
        CandidateSkill::factory()->create([
            'candidate_id' => $candidate->id,
            'skill_id' => $skill->id,
        ]);
    }

    expect($candidate->skills)->toHaveCount(3);
});

it('casts contact_links to array', function (): void {
    $candidate = Candidate::factory()->create([
        'contact_links' => ['linkedin' => 'https://linkedin.com/in/test'],
    ]);

    expect($candidate->contact_links)->toBeArray();
});

it('casts willing_to_relocate to boolean', function (): void {
    $candidate = Candidate::factory()->create(['willing_to_relocate' => true]);

    expect($candidate->willing_to_relocate)->toBeBool();
});

it('casts has_disability to boolean', function (): void {
    $candidate = Candidate::factory()->create(['has_disability' => false]);

    expect($candidate->has_disability)->toBeBool();
});

it('formats experience time as localized string', function (): void {
    $candidate = Candidate::factory()->create();

    WorkExperience::factory()->create([
        'candidate_id' => $candidate->id,
        'start_date' => now()->subMonths(18),
        'end_date' => now(),
        'is_currently_working_here' => false,
    ]);

    $formatted = $candidate->total_experience_formatted;

    expect($formatted)->toBeString()
        ->and($formatted)->not->toBeEmpty();
});

it('calculates individual experience duration correctly', function (): void {
    $candidate = Candidate::factory()->create();

    $experience = WorkExperience::factory()->create([
        'candidate_id' => $candidate->id,
        'start_date' => now()->subMonths(6),
        'end_date' => now(),
        'is_currently_working_here' => false,
    ]);

    $duration = $candidate->getExperienceDuration($experience);

    expect($duration)->toBeString()
        ->and($duration)->not->toBeEmpty();
});
