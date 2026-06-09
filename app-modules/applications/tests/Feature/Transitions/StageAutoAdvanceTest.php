<?php

declare(strict_types=1);

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Services\Transitions\TransitionData;
use He4rt\Recruitment\Stages\Enums\StageTypeEnum;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Users\User;

describe('stage auto-advances to mirror the status on the funnel ends', function (): void {
    it('extending an offer moves the stage to the offer-type stage', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->withStatus(ApplicationStatusEnum::InProgress)->create();

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::OfferExtended,
            'offer_amount' => 9500,
        ], $user->id);

        $application->current_step->handle($data);

        $fresh = $application->fresh()->load('currentStage');
        expect($fresh->status)->toBe(ApplicationStatusEnum::OfferExtended)
            ->and($fresh->currentStage->stage_type)->toBe(StageTypeEnum::Offer);
    });

    it('hiring moves the stage to the hired-type stage', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->withStatus(ApplicationStatusEnum::OfferAccepted)->create();

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Hired,
        ], $user->id);

        $application->current_step->handle($data);

        $fresh = $application->fresh()->load('currentStage');
        expect($fresh->status)->toBe(ApplicationStatusEnum::Hired)
            ->and($fresh->currentStage->stage_type)->toBe(StageTypeEnum::Hired);
    });

    it('withdrawing after acceptance keeps the current stage', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->withStatus(ApplicationStatusEnum::OfferAccepted)->create();
        $stageBefore = $application->current_stage_id;

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Withdrawn,
        ], $user->id);

        $application->current_step->handle($data);

        $fresh = $application->fresh();
        expect($fresh->status)->toBe(ApplicationStatusEnum::Withdrawn)
            ->and($fresh->current_stage_id)->toBe($stageBefore);
    });

    it('does not move when already sitting on a stage of the target type', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->withStatus(ApplicationStatusEnum::InProgress)->create();

        $offerStage = $application->requisition->stages()
            ->where('stage_type', StageTypeEnum::Offer)->firstOrFail();

        // A second, EARLIER offer stage — the auto-advance must not yank the candidate here.
        Stage::factory()->create([
            'job_requisition_id' => $application->requisition_id,
            'stage_type' => StageTypeEnum::Offer,
            'display_order' => 0,
            'active' => true,
        ]);

        $application->update(['current_stage_id' => $offerStage->id]);

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::OfferExtended,
            'offer_amount' => 5000,
        ], $user->id);

        Application::query()->findOrFail($application->id)->current_step->handle($data);

        expect($application->fresh()->current_stage_id)->toBe($offerStage->id);
    });

    it('hiring without a hired-type stage changes status only and does not throw', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->withStatus(ApplicationStatusEnum::OfferAccepted)->create();

        Stage::query()->where('job_requisition_id', $application->requisition_id)
            ->where('stage_type', StageTypeEnum::Hired)->delete();

        $reloaded = Application::query()->findOrFail($application->id);
        $stageBefore = $reloaded->current_stage_id;

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Hired,
        ], $user->id);

        expect(fn () => $reloaded->current_step->handle($data))->not->toThrow(Exception::class);

        $fresh = $application->fresh();
        expect($fresh->status)->toBe(ApplicationStatusEnum::Hired)
            ->and($fresh->current_stage_id)->toBe($stageBefore);
    });
});

describe('Application::firstStageOfType', function (): void {
    it('returns the first active stage of the type ordered by display_order', function (): void {
        $application = Application::factory()->withStatus(ApplicationStatusEnum::InProgress)->create();

        $stage = $application->firstStageOfType(StageTypeEnum::Hired);

        expect($stage)->not->toBeNull()
            ->and($stage->stage_type)->toBe(StageTypeEnum::Hired);
    });

    it('returns null when no stage of the type exists', function (): void {
        $application = Application::factory()->withStatus(ApplicationStatusEnum::InProgress)->create();

        Stage::query()->where('job_requisition_id', $application->requisition_id)
            ->where('stage_type', StageTypeEnum::Hired)->delete();

        $fresh = Application::query()->findOrFail($application->id);

        expect($fresh->firstStageOfType(StageTypeEnum::Hired))->toBeNull();
    });
});
