<?php

declare(strict_types=1);

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\CandidateSourceEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Feedback\Models\ApplicationComment;
use He4rt\Feedback\Models\Evaluation;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Screening\Models\ScreeningResponse;
use He4rt\Users\User;
use Illuminate\Database\UniqueConstraintViolationException;

it('can create an application', function (): void {
    $application = Application::factory()->create();

    expect($application)->toBeInstanceOf(Application::class)
        ->and($application->id)->not->toBeNull()
        ->and($application->status)->toBeInstanceOf(ApplicationStatusEnum::class)
        ->and($application->source)->toBeInstanceOf(CandidateSourceEnum::class)
        ->and($application->tracking_code)->not->toBeNull();
});

it('can create a rejected application', function (): void {
    $application = Application::factory()->rejected()->create();

    expect($application->status)->toBe(ApplicationStatusEnum::Rejected)
        ->and($application->rejected_at)->not->toBeNull()
        ->and($application->rejected_by)->not->toBeNull()
        ->and($application->rejection_reason_category)->toBeInstanceOf(RejectionReasonCategoryEnum::class)
        ->and($application->rejection_reason_details)->not->toBeNull();
});

it('can create an application with offer', function (): void {
    $application = Application::factory()->withOffer()->create();

    expect($application->status)->toBe(ApplicationStatusEnum::OfferExtended)
        ->and($application->offer_extended_at)->not->toBeNull()
        ->and($application->offer_extended_by)->not->toBeNull()
        ->and($application->offer_amount)->not->toBeNull()
        ->and($application->offer_response_deadline)->not->toBeNull();
});

it('belongs to a requisition', function (): void {
    $requisition = JobRequisition::factory()->create();
    $application = Application::factory()->create(['requisition_id' => $requisition->id]);

    expect($application->requisition)->toBeInstanceOf(JobRequisition::class)
        ->and($application->requisition->id)->toBe($requisition->id);
});

it('belongs to a candidate', function (): void {
    $candidate = Candidate::factory()->create();
    $application = Application::factory()->create(['candidate_id' => $candidate->id]);

    expect($application->candidate)->toBeInstanceOf(Candidate::class)
        ->and($application->candidate->id)->toBe($candidate->id);
});

it('belongs to a current stage', function (): void {
    $stage = Stage::factory()->create();
    $application = Application::factory()->create(['current_stage_id' => $stage->id]);

    expect($application->currentStage)->toBeInstanceOf(Stage::class)
        ->and($application->currentStage->id)->toBe($stage->id);
});

it('belongs to rejected by user', function (): void {
    $user = User::factory()->create();
    $application = Application::factory()->create([
        'status' => ApplicationStatusEnum::Rejected,
        'rejected_at' => now(),
        'rejected_by' => $user->id,
    ]);

    expect($application->rejectedBy)->toBeInstanceOf(User::class)
        ->and($application->rejectedBy->id)->toBe($user->id);
});

it('belongs to offer extended by user', function (): void {
    $user = User::factory()->create();
    $application = Application::factory()->create([
        'status' => ApplicationStatusEnum::OfferExtended,
        'offer_extended_at' => now(),
        'offer_extended_by' => $user->id,
    ]);

    expect($application->offerExtendedBy)->toBeInstanceOf(User::class)
        ->and($application->offerExtendedBy->id)->toBe($user->id);
});

it('has many screening responses', function (): void {
    $application = Application::factory()->create();
    ScreeningResponse::factory()->count(3)->create(['application_id' => $application->id]);

    expect($application->screeningResponses)->toHaveCount(3);
});

it('has many evaluations', function (): void {
    $application = Application::factory()->create();
    Evaluation::factory()->count(2)->create(['application_id' => $application->id]);

    expect($application->evaluations)->toHaveCount(2);
});

it('has many comments', function (): void {
    $application = Application::factory()->create();
    ApplicationComment::factory()->count(4)->create(['application_id' => $application->id]);

    expect($application->comments)->toHaveCount(4);
});

it('enforces unique requisition and candidate combination', function (): void {
    $requisition = JobRequisition::factory()->create();
    $candidate = Candidate::factory()->create();

    Application::factory()->create([
        'requisition_id' => $requisition->id,
        'candidate_id' => $candidate->id,
    ]);

    Application::factory()->create([
        'requisition_id' => $requisition->id,
        'candidate_id' => $candidate->id,
    ]);
})->throws(UniqueConstraintViolationException::class);
