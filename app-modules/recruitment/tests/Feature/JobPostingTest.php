<?php

declare(strict_types=1);

use He4rt\Recruitment\Requisitions\Models\JobPosting;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;

it('can create a job posting', function (): void {
    $posting = JobPosting::factory()->create();

    expect($posting)->toBeInstanceOf(JobPosting::class)
        ->and($posting->id)->not->toBeNull()
        ->and($posting->title)->not->toBeNull()
        ->and($posting->slug)->not->toBeNull();
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

it('uses soft deletes', function (): void {
    $posting = JobPosting::factory()->create();
    $posting->delete();

    expect($posting->deleted_at)->not->toBeNull()
        ->and(JobPosting::withTrashed()->find($posting->id))->not->toBeNull();
});
