<?php

declare(strict_types=1);

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Models\ApplicationStageHistory;
use He4rt\Applications\States\HiredApplicationState;
use He4rt\Applications\States\OfferDeclinedApplicationState;
use He4rt\Applications\States\TransitionData;
use He4rt\Applications\States\WithdrawnApplicationState;
use He4rt\Users\User;

describe('HiredApplicationState', function (): void {
    it('canChange() returns false (terminal state)', function (): void {
        $application = Application::factory()->create(['status' => ApplicationStatusEnum::Hired]);
        $transition = new HiredApplicationState($application);

        expect($transition->canChange())->toBeFalse();
    });

    it('choices() returns empty array (terminal state)', function (): void {
        $application = Application::factory()->create(['status' => ApplicationStatusEnum::Hired]);
        $transition = new HiredApplicationState($application);

        expect($transition->choices())->toBe([]);
    });

    it('processStep() sets status to Hired', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->create([
            'status' => ApplicationStatusEnum::Hired,
        ]);

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Hired,
        ], $user->id);

        $application->current_state->handle($data);

        expect($application->fresh()->status)->toBe(ApplicationStatusEnum::Hired);
    });

    it('creates a stage history record when handle() is called', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->create([
            'status' => ApplicationStatusEnum::Hired,
        ]);

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Hired,
        ], $user->id);

        $application->current_state->handle($data);

        expect(ApplicationStageHistory::query()->where('application_id', $application->id)->count())->toBe(1);
    });
});

describe('WithdrawnApplicationState', function (): void {
    it('canChange() returns false (terminal state)', function (): void {
        $application = Application::factory()->create(['status' => ApplicationStatusEnum::Withdrawn]);
        $transition = new WithdrawnApplicationState($application);

        expect($transition->canChange())->toBeFalse();
    });

    it('choices() returns empty array (terminal state)', function (): void {
        $application = Application::factory()->create(['status' => ApplicationStatusEnum::Withdrawn]);
        $transition = new WithdrawnApplicationState($application);

        expect($transition->choices())->toBe([]);
    });

    it('processStep() sets status to Withdrawn', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->create([
            'status' => ApplicationStatusEnum::Withdrawn,
        ]);

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Withdrawn,
        ], $user->id);

        $application->current_state->handle($data);

        expect($application->fresh()->status)->toBe(ApplicationStatusEnum::Withdrawn);
    });

    it('creates a stage history record when handle() is called', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->create([
            'status' => ApplicationStatusEnum::Withdrawn,
        ]);

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Withdrawn,
        ], $user->id);

        $application->current_state->handle($data);

        expect(ApplicationStageHistory::query()->where('application_id', $application->id)->count())->toBe(1);
    });
});

describe('OfferDeclinedApplicationState', function (): void {
    it('canChange() returns false (terminal state)', function (): void {
        $application = Application::factory()->create(['status' => ApplicationStatusEnum::OfferDeclined]);
        $transition = new OfferDeclinedApplicationState($application);

        expect($transition->canChange())->toBeFalse();
    });

    it('choices() returns empty array (terminal state)', function (): void {
        $application = Application::factory()->create(['status' => ApplicationStatusEnum::OfferDeclined]);
        $transition = new OfferDeclinedApplicationState($application);

        expect($transition->choices())->toBe([]);
    });

    it('processStep() sets status to OfferDeclined', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->create([
            'status' => ApplicationStatusEnum::OfferDeclined,
        ]);

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::OfferDeclined,
        ], $user->id);

        $application->current_state->handle($data);

        expect($application->fresh()->status)->toBe(ApplicationStatusEnum::OfferDeclined);
    });

    it('creates a stage history record when handle() is called', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->create([
            'status' => ApplicationStatusEnum::OfferDeclined,
        ]);

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::OfferDeclined,
        ], $user->id);

        $application->current_state->handle($data);

        expect(ApplicationStageHistory::query()->where('application_id', $application->id)->count())->toBe(1);
    });
});
