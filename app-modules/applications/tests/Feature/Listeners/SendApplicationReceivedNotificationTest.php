<?php

declare(strict_types=1);

use He4rt\Applications\DTOs\ApplicationDTO;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\CandidateSourceEnum;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Notifications\ApplicationReceivedNotification;
use He4rt\Applications\Services\Applications\StoreApplication;
use He4rt\Candidates\Models\Candidate;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use Illuminate\Support\Facades\Notification;

it('notifies the candidate user when an application is submitted', function (): void {
    Notification::fake();

    $candidate = Candidate::factory()->create();
    $requisition = JobRequisition::factory()->create();

    $application = resolve(StoreApplication::class)->execute(new ApplicationDTO(
        requisitionId: $requisition->getKey(),
        candidateId: $candidate->getKey(),
        teamId: $requisition->team_id,
        status: ApplicationStatusEnum::New,
        source: CandidateSourceEnum::CareerPage,
    ));

    Notification::assertSentTo($candidate->user, ApplicationReceivedNotification::class);
    expect($application->exists)->toBeTrue();
});

it('does not notify when an application is created without the submission flow', function (): void {
    Notification::fake();

    Application::factory()->create();

    Notification::assertNothingSent();
});
