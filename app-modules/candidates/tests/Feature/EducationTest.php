<?php

declare(strict_types=1);

use He4rt\Candidates\Models\Candidate;
use He4rt\Candidates\Models\Education;

it('can create an education record', function (): void {
    $education = Education::factory()->create();

    expect($education)->toBeInstanceOf(Education::class)
        ->and($education->id)->not->toBeNull()
        ->and($education->institution)->not->toBeNull()
        ->and($education->degree)->not->toBeNull()
        ->and($education->field_of_study)->not->toBeNull();
});

it('belongs to a candidate', function (): void {
    $candidate = Candidate::factory()->create();
    $education = Education::factory()->create(['candidate_id' => $candidate->id]);

    expect($education->candidate)->toBeInstanceOf(Candidate::class)
        ->and($education->candidate->id)->toBe($candidate->id);
});

it('can be accessed from candidate', function (): void {
    $candidate = Candidate::factory()->create();
    Education::factory()->count(2)->create(['candidate_id' => $candidate->id]);

    expect($candidate->degrees)->toHaveCount(2);
});

it('casts is_enrolled to boolean', function (): void {
    $education = Education::factory()->create(['is_enrolled' => true]);

    expect($education->is_enrolled)->toBeBool();
});

it('uses soft deletes', function (): void {
    $education = Education::factory()->create();
    $education->delete();

    expect($education->deleted_at)->not->toBeNull()
        ->and(Education::withTrashed()->find($education->id))->not->toBeNull();
});

it('returns null duration for a completed degree without an end date', function (): void {
    $education = Education::factory()->create([
        'is_enrolled' => false,
        'end_date' => null,
        'start_date' => now()->subMonths(10),
    ]);

    expect($education->durationInMonths())->toBeNull();
});

it('counts duration up to today while still enrolled', function (): void {
    $education = Education::factory()->create([
        'is_enrolled' => true,
        'start_date' => now()->subMonths(6),
    ]);

    expect($education->durationInMonths())->toBe(6);
});

it('counts duration between start and end for a completed degree', function (): void {
    $education = Education::factory()->create([
        'is_enrolled' => false,
        'start_date' => now()->subMonths(48),
        'end_date' => now()->subMonths(12),
    ]);

    expect($education->durationInMonths())->toBe(36);
});
