<?php

declare(strict_types=1);

use He4rt\Candidates\Casts\AsWorkExperienceMetadata;
use He4rt\Candidates\DTOs\WorkExperienceMetadata;
use He4rt\Candidates\Models\WorkExperience;

describe('WorkExperienceMetadata', function (): void {
    it('defaults skills to an empty list', function (): void {
        expect((new WorkExperienceMetadata())->skills)->toBe([]);
    });

    it('hydrates skills from an array', function (): void {
        $metadata = WorkExperienceMetadata::fromArray(['skills' => ['Gupy', 'Excel']]);

        expect($metadata->skills)->toBe(['Gupy', 'Excel']);
    });

    it('discards empty entries when hydrating', function (): void {
        $metadata = WorkExperienceMetadata::fromArray(['skills' => ['Gupy', '', null]]);

        expect($metadata->skills)->toBe(['Gupy']);
    });

    it('hydrates to an empty instance from an unknown shape', function (): void {
        expect(WorkExperienceMetadata::fromArray([])->skills)->toBe([])
            ->and(WorkExperienceMetadata::fromArray(['team_size' => 12])->skills)->toBe([]);
    });

    it('serializes to a single skills key', function (): void {
        $metadata = new WorkExperienceMetadata(['Gupy']);

        expect($metadata->toArray())->toBe(['skills' => ['Gupy']])
            ->and($metadata->jsonSerialize())->toBe(['skills' => ['Gupy']]);
    });
});

describe('AsWorkExperienceMetadata', function (): void {
    it('returns an empty instance when the column is null', function (): void {
        $value = new AsWorkExperienceMetadata()
            ->get(new WorkExperience(), 'metadata', null, []);

        expect($value)->toBeInstanceOf(WorkExperienceMetadata::class)
            ->and($value->skills)->toBe([]);
    });

    it('decodes a json string from the database', function (): void {
        $value = new AsWorkExperienceMetadata()
            ->get(new WorkExperience(), 'metadata', '{"skills":["Gupy"]}', []);

        expect($value->skills)->toBe(['Gupy']);
    });

    it('accepts an already decoded array', function (): void {
        $value = new AsWorkExperienceMetadata()
            ->get(new WorkExperience(), 'metadata', ['skills' => ['Excel']], []);

        expect($value->skills)->toBe(['Excel']);
    });

    it('encodes the value object for storage', function (): void {
        $stored = new AsWorkExperienceMetadata()
            ->set(new WorkExperience(), 'metadata', new WorkExperienceMetadata(['Gupy']), []);

        expect($stored)->toBe('{"skills":["Gupy"]}');
    });

    it('encodes a plain array for storage', function (): void {
        $stored = new AsWorkExperienceMetadata()
            ->set(new WorkExperience(), 'metadata', ['skills' => ['Gupy']], []);

        expect($stored)->toBe('{"skills":["Gupy"]}');
    });

    it('stores null when the value is null', function (): void {
        $stored = new AsWorkExperienceMetadata()
            ->set(new WorkExperience(), 'metadata', null, []);

        expect($stored)->toBeNull();
    });
});
