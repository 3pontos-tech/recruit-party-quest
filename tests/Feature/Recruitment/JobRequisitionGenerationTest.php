<?php

declare(strict_types=1);

use He4rt\Recruitment\Requisitions\Enums\JobGenerationStatus;
use He4rt\Recruitment\Requisitions\Events\JobRequisitionGenerationEvent;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use Illuminate\Support\Facades\Event;

it('broadcasts processing event when job is dispatched', function (): void {
    Event::fake();

    broadcast(new JobRequisitionGenerationEvent(
        JobGenerationStatus::Processing,
        '123'
    ));

    Event::assertDispatched(fn (JobRequisitionGenerationEvent $event) => $event->status === JobGenerationStatus::Processing
        && $event->userId === '123');
});

it('broadcasts processing event when job starts', function (): void {
    Event::fake();

    broadcast(new JobRequisitionGenerationEvent(
        JobGenerationStatus::Processing,
        '456'
    ));

    Event::assertDispatched(fn (JobRequisitionGenerationEvent $event) => $event->status === JobGenerationStatus::Processing);
});

it('broadcasts success event with job requisition when completed', function (): void {
    Event::fake();

    $jobRequisition = JobRequisition::factory()->create();

    broadcast(new JobRequisitionGenerationEvent(
        JobGenerationStatus::Success,
        '789',
        (string) $jobRequisition->id
    ));

    Event::assertDispatched(fn (JobRequisitionGenerationEvent $event) => $event->status === JobGenerationStatus::Success
        && $event->jobRequisitionId === (string) $jobRequisition->id);
});

it('broadcasts error event when job fails', function (): void {
    Event::fake();

    $errorMessage = 'AI service unavailable';

    broadcast(new JobRequisitionGenerationEvent(
        JobGenerationStatus::Error,
        '101112',
        null,
        $errorMessage
    ));

    Event::assertDispatched(fn (JobRequisitionGenerationEvent $event) => $event->status === JobGenerationStatus::Error
        && $event->errorMessage === $errorMessage);
});

it('event broadcasts to correct private channel', function (): void {
    $event = new JobRequisitionGenerationEvent(
        JobGenerationStatus::Processing,
        '123'
    );

    $channel = $event->broadcastOn();

    expect($channel->name)->toBe('private-job-requisition.generation.123');
});

it('event broadcasts with correct event name', function (): void {
    $event = new JobRequisitionGenerationEvent(
        JobGenerationStatus::Success,
        '123'
    );

    expect($event->broadcastAs())->toBe('success');
});

it('event broadcasts with correct payload structure', function (): void {
    $jobRequisition = JobRequisition::factory()->create();

    $event = new JobRequisitionGenerationEvent(
        JobGenerationStatus::Success,
        '123',
        (string) $jobRequisition->id,
        'Test error'
    );

    $payload = $event->broadcastWith();

    expect($payload)->toHaveKeys(['status', 'job_requisition_id', 'error_message'])
        ->and($payload['status'])->toBe('success')
        ->and($payload['job_requisition_id'])->toBe((string) $jobRequisition->id)
        ->and($payload['error_message'])->toBe('Test error');
});

it('job generation status enum has correct values', function (): void {
    expect(JobGenerationStatus::Processing->value)->toBe('processing')
        ->and(JobGenerationStatus::Success->value)->toBe('success')
        ->and(JobGenerationStatus::Error->value)->toBe('error');
});
