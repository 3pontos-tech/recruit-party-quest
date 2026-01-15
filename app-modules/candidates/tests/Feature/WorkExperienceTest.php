<?php

declare(strict_types=1);

use He4rt\Candidates\Models\Candidate;
use He4rt\Candidates\Models\WorkExperience;

it('can create a work experience record', function (): void {
    $workExperience = WorkExperience::factory()->create();

    expect($workExperience)->toBeInstanceOf(WorkExperience::class)
        ->and($workExperience->id)->not->toBeNull()
        ->and($workExperience->company_name)->not->toBeNull()
        ->and($workExperience->description)->not->toBeNull();
});

it('belongs to a candidate', function (): void {
    $candidate = Candidate::factory()->create();
    $workExperience = WorkExperience::factory()->create(['candidate_id' => $candidate->id]);

    expect($workExperience->candidate)->toBeInstanceOf(Candidate::class)
        ->and($workExperience->candidate->id)->toBe($candidate->id);
});

it('can be accessed from candidate', function (): void {
    $candidate = Candidate::factory()->create();
    WorkExperience::factory()->count(3)->create(['candidate_id' => $candidate->id]);

    expect($candidate->workExperiences)->toHaveCount(3);
});

it('casts is_currently_working_here to boolean', function (): void {
    $workExperience = WorkExperience::factory()->create(['is_currently_working_here' => true]);

    expect($workExperience->is_currently_working_here)->toBeBool();
});

it('casts metadata to array', function (): void {
    $workExperience = WorkExperience::factory()->create([
        'metadata' => ['key' => 'value'],
    ]);

    expect($workExperience->metadata)->toBeArray();
});

it('uses soft deletes', function (): void {
    $workExperience = WorkExperience::factory()->create();
    $workExperience->delete();

    expect($workExperience->deleted_at)->not->toBeNull()
        ->and(WorkExperience::withTrashed()->find($workExperience->id))->not->toBeNull();
});
