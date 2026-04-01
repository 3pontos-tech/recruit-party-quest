<?php

declare(strict_types=1);

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Models\ApplicationStageHistory;
use He4rt\Applications\Services\Transitions\HiredTransition;
use He4rt\Applications\Services\Transitions\OfferDeclinedTransition;
use He4rt\Applications\Services\Transitions\TransitionData;
use He4rt\Applications\Services\Transitions\WithdrawnTransition;
use He4rt\Users\User;

describe('HiredTransition', function (): void {
    it('canChange() returns false (terminal state)', function (): void {
        $application = Application::factory()->create(['status' => ApplicationStatusEnum::Hired]);
        $transition = new HiredTransition($application);

        expect($transition->canChange())->toBeFalse();
    });

    it('choices() returns empty array (terminal state)', function (): void {
        $application = Application::factory()->create(['status' => ApplicationStatusEnum::Hired]);
        $transition = new HiredTransition($application);

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

        $application->current_step->handle($data);

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

        $application->current_step->handle($data);

        expect(ApplicationStageHistory::query()->where('application_id', $application->id)->count())->toBe(1);
    });
});

describe('WithdrawnTransition', function (): void {
    it('canChange() returns false (terminal state)', function (): void {
        $application = Application::factory()->create(['status' => ApplicationStatusEnum::Withdrawn]);
        $transition = new WithdrawnTransition($application);

        expect($transition->canChange())->toBeFalse();
    });

    it('choices() returns empty array (terminal state)', function (): void {
        $application = Application::factory()->create(['status' => ApplicationStatusEnum::Withdrawn]);
        $transition = new WithdrawnTransition($application);

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

        $application->current_step->handle($data);

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

        $application->current_step->handle($data);

        expect(ApplicationStageHistory::query()->where('application_id', $application->id)->count())->toBe(1);
    });
});

describe('OfferDeclinedTransition', function (): void {
    it('canChange() returns false (terminal state)', function (): void {
        $application = Application::factory()->create(['status' => ApplicationStatusEnum::OfferDeclined]);
        $transition = new OfferDeclinedTransition($application);

        expect($transition->canChange())->toBeFalse();
    });

    it('choices() returns empty array (terminal state)', function (): void {
        $application = Application::factory()->create(['status' => ApplicationStatusEnum::OfferDeclined]);
        $transition = new OfferDeclinedTransition($application);

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

        $application->current_step->handle($data);

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

        $application->current_step->handle($data);

        expect(ApplicationStageHistory::query()->where('application_id', $application->id)->count())->toBe(1);
    });
});
