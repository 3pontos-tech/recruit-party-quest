<?php

declare(strict_types=1);

use He4rt\Candidates\Models\Candidate;
use He4rt\Candidates\Models\CandidateSkill;
use He4rt\Candidates\Models\Skill;

it('can create a skill', function (): void {
    $skill = Skill::factory()->create();

    expect($skill)->toBeInstanceOf(Skill::class)
        ->and($skill->id)->not->toBeNull()
        ->and($skill->name)->not->toBeNull()
        ->and($skill->category)->not->toBeNull();
});

it('has many candidates through candidate skills', function (): void {
    $skill = Skill::factory()->create();
    $candidates = Candidate::factory()->count(3)->create();

    foreach ($candidates as $candidate) {
        CandidateSkill::factory()->create([
            'skill_id' => $skill->id,
            'candidate_id' => $candidate->id,
        ]);
    }

    expect($skill->candidates)->toHaveCount(3);
});

it('uses soft deletes', function (): void {
    $skill = Skill::factory()->create();
    $skill->delete();

    expect($skill->deleted_at)->not->toBeNull()
        ->and(Skill::withTrashed()->find($skill->id))->not->toBeNull();
});
