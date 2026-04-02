<?php

declare(strict_types=1);

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Exceptions\InvalidTransitionException;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Services\Transitions\OfferAcceptedTransition;
use He4rt\Applications\Services\Transitions\TransitionData;
use He4rt\Users\User;

describe('OfferAcceptedTransition', function (): void {
    it('canChange() returns true', function (): void {
        $application = Application::factory()->create(['status' => ApplicationStatusEnum::OfferAccepted]);
        $transition = new OfferAcceptedTransition($application);

        expect($transition->canChange())->toBeTrue();
    });

    it('choices() contains only Hired', function (): void {
        $application = Application::factory()->create(['status' => ApplicationStatusEnum::OfferAccepted]);
        $transition = new OfferAcceptedTransition($application);

        expect(array_keys($transition->choices()))->toBe([ApplicationStatusEnum::Hired->value]);
    });

    it('processStep() sets status to Hired', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->create([
            'status' => ApplicationStatusEnum::OfferAccepted,
        ]);

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Hired,
        ], $user->id);

        $application->current_step->handle($data);

        expect($application->fresh()->status)->toBe(ApplicationStatusEnum::Hired);
    });

    it('throws InvalidTransitionException for illegal target status', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->create([
            'status' => ApplicationStatusEnum::OfferAccepted,
        ]);

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Rejected,
        ], $user->id);

        expect(fn () => $application->current_step->handle($data))->toThrow(InvalidTransitionException::class);
    });

    it('throws InvalidTransitionException for Withdrawn target status', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->create([
            'status' => ApplicationStatusEnum::OfferAccepted,
        ]);

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Withdrawn,
        ], $user->id);

        expect(fn () => $application->current_step->handle($data))->toThrow(InvalidTransitionException::class);
    });
});
