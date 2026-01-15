<?php

declare(strict_types=1);

use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;

it('can create a job posting', function (): void {
    $posting = JobPosting::factory()->create();

    expect($posting)->toBeInstanceOf(JobPosting::class)
        ->and($posting->id)->not->toBeNull()
        ->and($posting->title)->not->toBeNull()
        ->and($posting->slug)->not->toBeNull()
        ->and($posting->description)->not->toBeNull();
});

it('belongs to a job requisition', function (): void {
    $requisition = JobRequisition::factory()->create();
    $posting = JobPosting::factory()->create(['job_requisition_id' => $requisition->id]);

    expect($posting->jobRequisition)->toBeInstanceOf(JobRequisition::class)
        ->and($posting->jobRequisition->id)->toBe($requisition->id);
});

it('can be accessed from requisition', function (): void {
    $requisition = JobRequisition::factory()->create();
    $posting = JobPosting::factory()->create(['job_requisition_id' => $requisition->id]);

    expect($requisition->post)->toBeInstanceOf(JobPosting::class)
        ->and($requisition->post->id)->toBe($posting->id);
});

it('casts responsibilities to array', function (): void {
    $posting = JobPosting::factory()->create([
        'responsibilities' => ['Lead team', 'Write code'],
    ]);

    expect($posting->responsibilities)->toBeArray();
});

it('casts required_qualifications to array', function (): void {
    $posting = JobPosting::factory()->create([
        'required_qualifications' => ['PHP', 'Laravel'],
    ]);

    expect($posting->required_qualifications)->toBeArray();
});

it('casts preferred_qualifications to array', function (): void {
    $posting = JobPosting::factory()->create([
        'preferred_qualifications' => ['Vue.js', 'React'],
    ]);

    expect($posting->preferred_qualifications)->toBeArray();
});

it('casts benefits to array', function (): void {
    $posting = JobPosting::factory()->create([
        'benefits' => ['Health insurance', '401k'],
    ]);

    expect($posting->benefits)->toBeArray();
});

it('casts is_disability_confident to boolean', function (): void {
    $posting = JobPosting::factory()->create(['is_disability_confident' => true]);

    expect($posting->is_disability_confident)->toBeBool();
});

it('uses soft deletes', function (): void {
    $posting = JobPosting::factory()->create();
    $posting->delete();

    expect($posting->deleted_at)->not->toBeNull()
        ->and(JobPosting::withTrashed()->find($posting->id))->not->toBeNull();
});
