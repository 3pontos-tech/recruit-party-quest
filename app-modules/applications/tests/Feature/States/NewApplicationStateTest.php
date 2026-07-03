<?php

declare(strict_types=1);

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
use He4rt\Applications\Exceptions\InvalidTransitionException;
use He4rt\Applications\Models\Application;
use He4rt\Applications\States\NewApplicationState;
use He4rt\Applications\States\TransitionData;
use He4rt\Users\User;
use Illuminate\Support\Facades\Date;

describe('NewApplicationState', function (): void {
    it('canChange() returns true', function (): void {
        $application = Application::factory()->withStatus(ApplicationStatusEnum::New)->create();
        $transition = new NewApplicationState($application);

        expect($transition->canChange())->toBeTrue();
    });

    it('choices() contains InReview, Rejected and Withdrawn', function (): void {
        $application = Application::factory()->withStatus(ApplicationStatusEnum::New)->create();
        $transition = new NewApplicationState($application);

        expect(array_keys($transition->choices()))->toContain(
            ApplicationStatusEnum::InReview->value,
            ApplicationStatusEnum::Rejected->value,
            ApplicationStatusEnum::Withdrawn->value,
        );
    });

    it('New → InReview sets status and first available stage', function (): void {
        $user = User::factory()->create();

        [$application, $stage] = Application::factory()
            ->withStatus(ApplicationStatusEnum::New)
            ->withIsolatedStages()
            ->createWithStage();

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::InReview,
        ], $user->id);

        $application->current_state->handle($data);

        $application->refresh();

        expect($application->status)->toBe(ApplicationStatusEnum::InReview)
            ->and($application->current_stage_id)->toBe($stage->id);
    });

    it('New → InReview with no stages available keeps current_stage_id as null', function (): void {
        $user = User::factory()->create();

        $application = Application::factory()
            ->withStatus(ApplicationStatusEnum::New)
            ->withNoStages()
            ->create();

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::InReview,
        ], $user->id);

        $application->current_state->handle($data);

        $application->refresh();

        expect($application->status)->toBe(ApplicationStatusEnum::InReview)
            ->and($application->current_stage_id)->toBeNull();
    });

    it('New → Withdrawn sets status to Withdrawn', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->withStatus(ApplicationStatusEnum::New)->create();

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Withdrawn,
        ], $user->id);

        $application->current_state->handle($data);

        expect($application->fresh()->status)->toBe(ApplicationStatusEnum::Withdrawn);
    });

    it('New → Rejected writes all rejection fields', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->withStatus(ApplicationStatusEnum::New)->create();

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Rejected,
            'rejection_reason_category' => RejectionReasonCategoryEnum::Qualifications,
            'rejection_reason_details' => 'Does not meet minimum requirements',
            'rejected_at' => Date::parse('2026-01-15 10:00:00'),
        ], $user->id);

        $application->current_state->handle($data);

        $application->refresh();

        expect($application->status)->toBe(ApplicationStatusEnum::Rejected)
            ->and($application->rejected_by)->toBe($user->id)
            ->and($application->rejection_reason_category)->toBe(RejectionReasonCategoryEnum::Qualifications)
            ->and($application->rejection_reason_details)->toBe('Does not meet minimum requirements');
    });

    it('throws InvalidTransitionException for illegal target status', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->withStatus(ApplicationStatusEnum::New)->create();

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Hired,
        ], $user->id);

        expect(fn () => $application->current_state->handle($data))->toThrow(InvalidTransitionException::class);
    });
});
