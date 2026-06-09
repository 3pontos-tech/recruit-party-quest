<?php

declare(strict_types=1);

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Services\Transitions\TransitionData;
use He4rt\Users\User;

describe('stage history records the status transition', function (): void {
    it('persists from_status and to_status on the history row', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->withOffer()->create();

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::OfferAccepted,
        ], $user->id);

        $application->current_step->handle($data);

        $row = $application->stageHistory()->latest()->first();

        expect($row->from_status)->toBe(ApplicationStatusEnum::OfferExtended)
            ->and($row->to_status)->toBe(ApplicationStatusEnum::OfferAccepted);
    });

    it('records a status-only change with the same stage on both sides', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->withOffer()->create();
        $stageBefore = $application->current_stage_id;

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::OfferDeclined,
        ], $user->id);

        $application->current_step->handle($data);

        $row = $application->stageHistory()->latest()->first();

        expect($row->from_stage_id)->toBe($stageBefore)
            ->and($row->to_stage_id)->toBe($stageBefore)
            ->and($row->from_status)->toBe(ApplicationStatusEnum::OfferExtended)
            ->and($row->to_status)->toBe(ApplicationStatusEnum::OfferDeclined);
    });
});
