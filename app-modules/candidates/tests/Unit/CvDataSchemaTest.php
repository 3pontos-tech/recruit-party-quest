<?php

declare(strict_types=1);

use He4rt\Candidates\AI\Schema\CvDataSchema;
use He4rt\Candidates\Enums\ResumeErrorReasons;
use Prism\Prism\Providers\Gemini\Maps\SchemaMap;

function cvSchemaArray(): array
{
    return new SchemaMap(CvDataSchema::make(ResumeErrorReasons::NotAnCV))->toArray();
}

it('declares the minimum required fields for a work experience', function (): void {
    $experience = cvSchemaArray()['properties']['work_experiences']['items'];

    expect($experience['required'])
        ->toBe(['company_name', 'start_date', 'is_currently_working_here']);
});

it('exposes position and skills as extractable fields', function (): void {
    $experience = cvSchemaArray()['properties']['work_experiences']['items'];

    expect($experience['properties'])->toHaveKeys([
        'company_name', 'position', 'description', 'skills',
        'start_date', 'end_date', 'is_currently_working_here',
    ])->and($experience['properties']['skills']['type'])->toBe('array');
});

it('keeps position and skills optional so the model never invents them', function (): void {
    $experience = cvSchemaArray()['properties']['work_experiences']['items'];

    expect($experience['required'])
        ->not->toContain('position')
        ->not->toContain('skills')
        ->not->toContain('description');
});

it('requires is_cv at the root so validate() always has the flag', function (): void {
    expect(cvSchemaArray()['required'])->toContain('is_cv');
});

it('declares required fields for education', function (): void {
    $education = cvSchemaArray()['properties']['education']['items'];

    expect($education['required'])->toBe(['institution', 'start_date', 'is_enrolled']);
});

it('lets every date be null so the model never fills it with a placeholder', function (string $group): void {
    $properties = cvSchemaArray()['properties'][$group]['items']['properties'];

    expect($properties['start_date']['nullable'])->toBeTrue()
        ->and($properties['end_date']['nullable'])->toBeTrue();
})->with(['work_experiences', 'education']);
