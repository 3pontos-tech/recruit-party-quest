<?php

declare(strict_types=1);

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
use He4rt\Applications\Exceptions\InvalidTransitionException;
use He4rt\Applications\Exceptions\MissingTransitionDataException;
use He4rt\Applications\Models\Application;
use He4rt\Applications\States\InReviewApplicationState;
use He4rt\Applications\States\TransitionData;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Users\User;

describe('InReviewApplicationState', function (): void {
    it('canChange() returns true', function (): void {
        $application = Application::factory()->withStatus(ApplicationStatusEnum::InReview)->create();
        $transition = new InReviewApplicationState($application);

        expect($transition->canChange())->toBeTrue();
    });

    it('choices() contains InProgress, Rejected and Withdrawn', function (): void {
        $application = Application::factory()->withStatus(ApplicationStatusEnum::InReview)->create();
        $transition = new InReviewApplicationState($application);

        expect(array_keys($transition->choices()))->toContain(
            ApplicationStatusEnum::InProgress->value,
            ApplicationStatusEnum::Rejected->value,
            ApplicationStatusEnum::Withdrawn->value,
        );
    });

    it('InReview → InProgress advances to next stage', function (): void {
        $user = User::factory()->create();

        [$application, $stage] = Application::factory()
            ->withStatus(ApplicationStatusEnum::InReview)
            ->withIsolatedStages()
            ->createWithStage(['current_stage_id' => null]);

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::InProgress,
        ], $user->id);

        $application->current_state->handle($data);

        $application->refresh();

        expect($application->status)->toBe(ApplicationStatusEnum::InProgress)
            ->and($application->current_stage_id)->toBe($stage->id);
    });

    it('InReview → InProgress with explicit to_stage_id uses that stage', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->withStatus(ApplicationStatusEnum::InReview)->create();
        $specificStage = Stage::factory()->create([
            'job_requisition_id' => $application->requisition_id,
            'display_order' => 5,
        ]);

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::InProgress,
            'to_stage_id' => $specificStage->id,
        ], $user->id);

        $application->current_state->handle($data);

        expect($application->fresh()->current_stage_id)->toBe($specificStage->id);
    });

    it('InReview → Rejected without rejection_reason_category throws MissingTransitionDataException', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->withStatus(ApplicationStatusEnum::InReview)->create();

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Rejected,
        ], $user->id);

        expect(fn () => $application->current_state->handle($data))
            ->toThrow(MissingTransitionDataException::class);
    });

    it('InReview → Rejected with category writes all rejection fields', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->withStatus(ApplicationStatusEnum::InReview)->create();

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Rejected,
            'rejection_reason_category' => RejectionReasonCategoryEnum::Experience,
            'rejection_reason_details' => 'Insufficient experience',
        ], $user->id);

        $application->current_state->handle($data);

        $application->refresh();

        expect($application->status)->toBe(ApplicationStatusEnum::Rejected)
            ->and($application->rejected_by)->toBe($user->id)
            ->and($application->rejection_reason_category)->toBe(RejectionReasonCategoryEnum::Experience)
            ->and($application->rejection_reason_details)->toBe('Insufficient experience')
            ->and($application->rejected_at)->not->toBeNull();
    });

    it('InReview → Withdrawn sets status to Withdrawn', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->withStatus(ApplicationStatusEnum::InReview)->create();

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Withdrawn,
        ], $user->id);

        $application->current_state->handle($data);

        expect($application->fresh()->status)->toBe(ApplicationStatusEnum::Withdrawn);
    });

    it('throws InvalidTransitionException for illegal target status', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->withStatus(ApplicationStatusEnum::InReview)->create();

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Hired,
        ], $user->id);

        expect(fn () => $application->current_state->handle($data))->toThrow(InvalidTransitionException::class);
    });

    it('rejected_at defaults to now() when not provided', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->withStatus(ApplicationStatusEnum::InReview)->create();

        $before = now()->subSecond();

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Rejected,
            'rejection_reason_category' => RejectionReasonCategoryEnum::Other,
        ], $user->id);

        $application->current_state->handle($data);

        expect($application->fresh()->rejected_at)->not->toBeNull()
            ->and($application->fresh()->rejected_at->isAfter($before))->toBeTrue();
    });
});
