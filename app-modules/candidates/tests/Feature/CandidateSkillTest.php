<?php

declare(strict_types=1);

use He4rt\Candidates\Models\Candidate;
use He4rt\Candidates\Models\CandidateSkill;
use He4rt\Candidates\Models\Skill;

it('can create a candidate skill', function (): void {
    $candidateSkill = CandidateSkill::factory()->create();

    expect($candidateSkill)->toBeInstanceOf(CandidateSkill::class)
        ->and($candidateSkill->id)->not->toBeNull()
        ->and($candidateSkill->proficiency_level)->not->toBeNull()
        ->and($candidateSkill->years_of_experience)->not->toBeNull();
});

it('belongs to a skill', function (): void {
    $skill = Skill::factory()->create();
    $candidateSkill = CandidateSkill::factory()->create(['skill_id' => $skill->id]);

    expect($candidateSkill->skill)->toBeInstanceOf(Skill::class)
        ->and($candidateSkill->skill->id)->toBe($skill->id);
});

it('belongs to a candidate', function (): void {
    $candidate = Candidate::factory()->create();
    $candidateSkill = CandidateSkill::factory()->create(['candidate_id' => $candidate->id]);

    expect($candidateSkill->candidate)->toBeInstanceOf(Candidate::class)
        ->and($candidateSkill->candidate->id)->toBe($candidate->id);
});

it('has proficiency level between 1 and 5', function (): void {
    $candidateSkill = CandidateSkill::factory()->create();

    expect($candidateSkill->proficiency_level)->toBeGreaterThanOrEqual(1)
        ->and($candidateSkill->proficiency_level)->toBeLessThanOrEqual(5);
});
