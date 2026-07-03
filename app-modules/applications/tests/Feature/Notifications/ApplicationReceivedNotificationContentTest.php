<?php

declare(strict_types=1);

use He4rt\Applications\Mail\ApplicationReceivedMail;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Notifications\ApplicationReceivedNotification;
use He4rt\Recruitment\Requisitions\Models\JobPosting;
use Illuminate\Contracts\Queue\ShouldQueue;

it('targets mail and database channels and is queued', function (): void {
    $application = Application::factory()->create();
    JobPosting::factory()->for($application->requisition, 'jobRequisition')->create();
    $application->load('requisition.post');
    $user = $application->candidate->user;

    $notification = new ApplicationReceivedNotification($application);

    expect($notification)->toBeInstanceOf(ShouldQueue::class)
        ->and($notification->via($user))->toBe(['mail', 'database'])
        ->and($notification->toMail($user))->toBeInstanceOf(ApplicationReceivedMail::class);

    $database = $notification->toDatabase($user);

    expect($database)->toBeArray()
        ->and($database['body'])->toContain($application->requisition->post->title);
});

it('falls back to a generic job title in the database payload when the posting is missing', function (): void {
    $application = Application::factory()->create();
    $application->load('requisition.post');

    $user = $application->candidate->user;

    expect($application->requisition->post)->toBeNull();

    $fallback = __('applications::filament.emails.application_received.job_fallback');

    $database = new ApplicationReceivedNotification($application)->toDatabase($user);

    expect($database['body'])->toContain($fallback);
});
