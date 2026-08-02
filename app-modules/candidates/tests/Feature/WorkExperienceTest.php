<?php

declare(strict_types=1);

use He4rt\Candidates\DTOs\WorkExperienceMetadata;
use He4rt\Candidates\Models\Candidate;
use He4rt\Candidates\Models\WorkExperience;
use Illuminate\Support\Facades\DB;

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

it('casts metadata to the value object when given a plain array', function (): void {
    $workExperience = WorkExperience::factory()->create([
        'metadata' => ['skills' => ['Excel']],
    ]);

    expect($workExperience->metadata)
        ->toBeInstanceOf(WorkExperienceMetadata::class)
        ->and($workExperience->metadata->skills)->toBe(['Excel']);
});

it('uses soft deletes', function (): void {
    $workExperience = WorkExperience::factory()->create();
    $workExperience->delete();

    expect($workExperience->deleted_at)->not->toBeNull()
        ->and(WorkExperience::withTrashed()->find($workExperience->id))->not->toBeNull();
});

it('returns null duration for a past role without an end date', function (): void {
    $workExperience = WorkExperience::factory()->create([
        'is_currently_working_here' => false,
        'end_date' => null,
        'start_date' => now()->subMonths(10),
    ]);

    expect($workExperience->durationInMonths())->toBeNull();
});

it('counts duration up to today for a currently held role', function (): void {
    $workExperience = WorkExperience::factory()->create([
        'is_currently_working_here' => true,
        'start_date' => now()->subMonths(6),
    ]);

    expect($workExperience->durationInMonths())->toBe(6);
});

it('counts duration between start and end for a finished role', function (): void {
    $workExperience = WorkExperience::factory()->create([
        'is_currently_working_here' => false,
        'start_date' => now()->subMonths(24),
        'end_date' => now()->subMonths(12),
    ]);

    expect($workExperience->durationInMonths())->toBe(12);
});

it('persists and reads back the position column', function (): void {
    $experience = WorkExperience::factory()->create(['position' => 'Analista de RH Pleno']);

    expect($experience->fresh()->position)->toBe('Analista de RH Pleno');
});

it('allows a null position for legacy records', function (): void {
    $experience = WorkExperience::factory()->create(['position' => null]);

    expect($experience->fresh()->position)->toBeNull();
});

it('casts metadata to the value object', function (): void {
    $experience = WorkExperience::factory()->create([
        'metadata' => new WorkExperienceMetadata(['Gupy', 'Excel']),
    ]);

    expect($experience->fresh()->metadata)
        ->toBeInstanceOf(WorkExperienceMetadata::class)
        ->and($experience->fresh()->metadata->skills)->toBe(['Gupy', 'Excel']);
});

it('returns an empty metadata object when the column is null', function (): void {
    $experience = WorkExperience::factory()->create(['metadata' => null]);

    expect($experience->fresh()->metadata)
        ->toBeInstanceOf(WorkExperienceMetadata::class)
        ->and($experience->fresh()->metadata->skills)->toBe([]);
});

it('no longer seeds team_size or project_type', function (): void {
    $experience = WorkExperience::factory()->create();

    $raw = json_decode(
        (string) DB::table('candidate_work_experiences')
            ->where('id', $experience->getKey())
            ->value('metadata'),
        true,
    );

    expect($raw)->toHaveKey('skills')
        ->not->toHaveKey('team_size')
        ->not->toHaveKey('project_type')
        ->not->toHaveKey('position');   // agora é coluna
});
