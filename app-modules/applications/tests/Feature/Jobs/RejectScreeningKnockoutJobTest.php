<?php

declare(strict_types=1);

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
use He4rt\Applications\Jobs\RejectScreeningKnockoutJob;
use He4rt\Applications\Models\Application;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;

function newApplicationForRejectJob(JobRequisition $req): Application
{
    $first = $req->stages()->orderBy('display_order')->first();

    return Application::factory()->create([
        'requisition_id' => $req->id,
        'team_id' => $req->team_id,
        'status' => ApplicationStatusEnum::New,
        'current_stage_id' => $first->id,
    ]);
}

it('rejects a New application when the flag is still on', function (): void {
    $req = JobRequisition::factory()->create(['auto_screening_transition' => true]);
    $application = newApplicationForRejectJob($req);

    new RejectScreeningKnockoutJob($application)->handle();

    $application->refresh();

    expect($application->status)->toBe(ApplicationStatusEnum::Rejected)
        ->and($application->rejection_reason_category)->toBe(RejectionReasonCategoryEnum::ScreeningKnockout)
        ->and($application->rejected_by)->toBeNull();
});

it('is a no-op when status is no longer New', function (): void {
    $req = JobRequisition::factory()->create(['auto_screening_transition' => true]);
    $application = newApplicationForRejectJob($req);
    $application->update(['status' => ApplicationStatusEnum::InReview]);

    new RejectScreeningKnockoutJob($application)->handle();

    expect($application->fresh()->status)->toBe(ApplicationStatusEnum::InReview);
});

it('is a no-op when the requisition flag has been turned off', function (): void {
    $req = JobRequisition::factory()->create(['auto_screening_transition' => true]);
    $application = newApplicationForRejectJob($req);

    $req->update(['auto_screening_transition' => false]);

    new RejectScreeningKnockoutJob($application)->handle();

    expect($application->fresh()->status)->toBe(ApplicationStatusEnum::New);
});

it('is idempotent: a second job execution does not change anything', function (): void {
    $req = JobRequisition::factory()->create(['auto_screening_transition' => true]);
    $application = newApplicationForRejectJob($req);

    new RejectScreeningKnockoutJob($application)->handle();
    $rejectedAt = $application->fresh()->rejected_at;

    new RejectScreeningKnockoutJob($application->fresh())->handle();

    $application->refresh();

    expect($application->status)->toBe(ApplicationStatusEnum::Rejected)
        ->and($application->rejected_at?->equalTo($rejectedAt))->toBeTrue();
});
