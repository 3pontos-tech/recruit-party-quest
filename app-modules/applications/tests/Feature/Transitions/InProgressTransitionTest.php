<?php

declare(strict_types=1);

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
use He4rt\Applications\Exceptions\InvalidTransitionException;
use He4rt\Applications\Exceptions\MissingTransitionDataException;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Services\Transitions\InProgressTransition;
use He4rt\Applications\Services\Transitions\TransitionData;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Users\User;

describe('InProgressTransition', function (): void {
    it('canChange() returns true', function (): void {
        $application = Application::factory()->withStatus(ApplicationStatusEnum::InProgress)->create();
        $transition = new InProgressTransition($application);

        expect($transition->canChange())->toBeTrue();
    });

    it('choices() contains InProgress, OfferExtended, Rejected and Withdrawn', function (): void {
        $application = Application::factory()->withStatus(ApplicationStatusEnum::InProgress)->create();
        $transition = new InProgressTransition($application);

        expect(array_keys($transition->choices()))->toContain(
            ApplicationStatusEnum::InProgress->value,
            ApplicationStatusEnum::OfferExtended->value,
            ApplicationStatusEnum::Rejected->value,
            ApplicationStatusEnum::Withdrawn->value,
        );
    });

    it('InProgress → InProgress via to_stage_id updates current_stage_id', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->withStatus(ApplicationStatusEnum::InProgress)->create();

        $current = Stage::factory()->create([
            'job_requisition_id' => $application->requisition_id,
            'display_order' => 1,
            'active' => true,
        ]);
        $application->update(['current_stage_id' => $current->id]);

        $targetStage = Stage::factory()->create([
            'job_requisition_id' => $application->requisition_id,
            'display_order' => 10,
            'active' => true,
        ]);

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::InProgress,
            'to_stage_id' => $targetStage->id,
        ], $user->id);

        $application->current_step->handle($data);

        $application->refresh();

        expect($application->status)->toBe(ApplicationStatusEnum::InProgress)
            ->and($application->current_stage_id)->toBe($targetStage->id);
    });

    it('rejects a move to an earlier or same-order stage and keeps current_stage_id', function (int $targetOrder): void {
        $user = User::factory()->create();
        $application = Application::factory()->withStatus(ApplicationStatusEnum::InProgress)->create();

        $current = Stage::factory()->create([
            'job_requisition_id' => $application->requisition_id,
            'display_order' => 5,
            'active' => true,
        ]);
        $application->update(['current_stage_id' => $current->id]);

        $target = Stage::factory()->create([
            'job_requisition_id' => $application->requisition_id,
            'display_order' => $targetOrder,
            'active' => true,
        ]);

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::InProgress,
            'to_stage_id' => $target->id,
        ], $user->id);

        expect(fn () => $application->current_step->handle($data))
            ->toThrow(InvalidTransitionException::class);

        expect($application->fresh()->current_stage_id)->toBe($current->id);
    })->with([
        'earlier stage' => 3,
        'same-order stage' => 5,
    ]);

    it('rejects a move to an inactive stage and keeps current_stage_id', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->withStatus(ApplicationStatusEnum::InProgress)->create();

        $current = Stage::factory()->create([
            'job_requisition_id' => $application->requisition_id,
            'display_order' => 5,
            'active' => true,
        ]);
        $application->update(['current_stage_id' => $current->id]);

        $inactive = Stage::factory()->create([
            'job_requisition_id' => $application->requisition_id,
            'display_order' => 9,
            'active' => false,
        ]);

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::InProgress,
            'to_stage_id' => $inactive->id,
        ], $user->id);

        expect(fn () => $application->current_step->handle($data))
            ->toThrow(InvalidTransitionException::class);

        expect($application->fresh()->current_stage_id)->toBe($current->id);
    });

    it('rejects a move to a stage from another requisition and keeps current_stage_id', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->withStatus(ApplicationStatusEnum::InProgress)->create();

        $current = Stage::factory()->create([
            'job_requisition_id' => $application->requisition_id,
            'display_order' => 5,
            'active' => true,
        ]);
        $application->update(['current_stage_id' => $current->id]);

        // Belongs to a brand-new requisition (own JobRequisition::factory()), so it is
        // forward and active but must still be refused as cross-requisition.
        $foreignStage = Stage::factory()->create([
            'display_order' => 9,
            'active' => true,
        ]);

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::InProgress,
            'to_stage_id' => $foreignStage->id,
        ], $user->id);

        expect(fn () => $application->current_step->handle($data))
            ->toThrow(InvalidTransitionException::class);

        expect($application->fresh()->current_stage_id)->toBe($current->id);
    });

    it('InProgress → InProgress via advance_stage moves to next stage', function (): void {
        $user = User::factory()->create();

        [$application, $stage] = Application::factory()
            ->withStatus(ApplicationStatusEnum::InProgress)
            ->withIsolatedStages()
            ->createWithStage(['current_stage_id' => null]);

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::InProgress,
            'advance_stage' => true,
        ], $user->id);

        $application->current_step->handle($data);

        expect($application->fresh()->current_stage_id)->toBe($stage->id);
    });

    it('InProgress → InProgress with no target stage throws InvalidTransitionException', function (): void {
        $user = User::factory()->create();

        $application = Application::factory()
            ->withStatus(ApplicationStatusEnum::InProgress)
            ->withNoStages()
            ->create();

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::InProgress,
            'advance_stage' => true,
        ], $user->id);

        expect(fn () => $application->current_step->handle($data))->toThrow(InvalidTransitionException::class);
    });

    it('InProgress → OfferExtended writes all offer fields', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->withStatus(ApplicationStatusEnum::InProgress)->create();

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::OfferExtended,
            'offer_amount' => '75000',
            'offer_extended_at' => '2026-03-01 09:00:00',
            'offer_response_deadline' => '2026-03-08 00:00:00',
        ], $user->id);

        $application->current_step->handle($data);

        $application->refresh();

        expect($application->status)->toBe(ApplicationStatusEnum::OfferExtended)
            ->and((float) $application->offer_amount)->toBe(75000.0)
            ->and($application->offer_extended_by)->toBe($user->id)
            ->and($application->offer_extended_at)->not->toBeNull()
            ->and($application->offer_response_deadline)->not->toBeNull();
    });

    it('InProgress → OfferExtended defaults offer_extended_at to now() when absent', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->withStatus(ApplicationStatusEnum::InProgress)->create();
        $before = now()->subSecond();

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::OfferExtended,
        ], $user->id);

        $application->current_step->handle($data);

        expect($application->fresh()->offer_extended_at->isAfter($before))->toBeTrue();
    });

    it('InProgress → Rejected without rejection_reason_category throws MissingTransitionDataException', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->withStatus(ApplicationStatusEnum::InProgress)->create();

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Rejected,
        ], $user->id);

        expect(fn () => $application->current_step->handle($data))
            ->toThrow(MissingTransitionDataException::class);
    });

    it('InProgress → Rejected with category writes all rejection fields', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->withStatus(ApplicationStatusEnum::InProgress)->create();

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Rejected,
            'rejection_reason_category' => RejectionReasonCategoryEnum::CultureFit->value,
            'rejection_reason_details' => 'Not a good cultural fit',
        ], $user->id);

        $application->current_step->handle($data);

        $application->refresh();

        expect($application->status)->toBe(ApplicationStatusEnum::Rejected)
            ->and($application->rejected_by)->toBe($user->id)
            ->and($application->rejection_reason_category)->toBe(RejectionReasonCategoryEnum::CultureFit)
            ->and($application->rejection_reason_details)->toBe('Not a good cultural fit')
            ->and($application->rejected_at)->not->toBeNull();
    });

    it('InProgress → Withdrawn sets status to Withdrawn', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->withStatus(ApplicationStatusEnum::InProgress)->create();

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Withdrawn,
        ], $user->id);

        $application->current_step->handle($data);

        expect($application->fresh()->status)->toBe(ApplicationStatusEnum::Withdrawn);
    });

    it('throws InvalidTransitionException for illegal target status', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->withStatus(ApplicationStatusEnum::InProgress)->create();

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Hired,
        ], $user->id);

        expect(fn () => $application->current_step->handle($data))->toThrow(InvalidTransitionException::class);
    });
});
