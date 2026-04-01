<?php

declare(strict_types=1);

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Exceptions\InvalidTransitionException;
use He4rt\Applications\Exceptions\MissingTransitionDataException;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Services\Transitions\OfferExtendedTransition;
use He4rt\Applications\Services\Transitions\TransitionData;
use He4rt\Recruitment\Stages\Models\Stage;
use He4rt\Users\User;

describe('OfferExtendedTransition', function (): void {
    it('canChange() returns true', function (): void {
        $application = Application::factory()->withOffer()->create();
        $transition = new OfferExtendedTransition($application);

        expect($transition->canChange())->toBeTrue();
    });

    it('choices() contains OfferAccepted, OfferDeclined and Withdrawn', function (): void {
        $application = Application::factory()->withOffer()->create();
        $transition = new OfferExtendedTransition($application);

        expect(array_keys($transition->choices()))->toContain(
            ApplicationStatusEnum::OfferAccepted->value,
            ApplicationStatusEnum::OfferDeclined->value,
            ApplicationStatusEnum::Withdrawn->value,
        );
    });

    it('processStep() keeps status as OfferExtended (no change to model status in this step)', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->withOffer()->create();

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::OfferAccepted,
            'to_stage_id' => Stage::factory()->create(['job_requisition_id' => $application->requisition_id])->id,
        ], $user->id);

        // OfferExtendedTransition::processStep() updates to OfferExtended (re-saves same status)
        $application->current_step->handle($data);

        expect($application->fresh()->status)->toBe(ApplicationStatusEnum::OfferExtended);
    });

    it('OfferExtended → OfferAccepted without to_stage_id throws MissingTransitionDataException', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->withOffer()->create();

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::OfferAccepted,
            // no to_stage_id
        ], $user->id);

        expect(fn () => $application->current_step->handle($data))
            ->toThrow(MissingTransitionDataException::class);
    });

    it('OfferExtended → OfferAccepted with to_stage_id succeeds validation', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->withOffer()->create();
        $stage = Stage::factory()->create(['job_requisition_id' => $application->requisition_id]);

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::OfferAccepted,
            'to_stage_id' => $stage->id,
        ], $user->id);

        expect(fn () => $application->current_step->handle($data))->not->toThrow(MissingTransitionDataException::class);
    });

    it('OfferExtended → OfferDeclined succeeds without any extra data', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->withOffer()->create();

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::OfferDeclined,
        ], $user->id);

        expect(fn () => $application->current_step->handle($data))->not->toThrow(Exception::class);
    });

    it('OfferExtended → Withdrawn succeeds without any extra data', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->withOffer()->create();

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Withdrawn,
        ], $user->id);

        expect(fn () => $application->current_step->handle($data))->not->toThrow(Exception::class);
    });

    it('throws InvalidTransitionException for illegal target status', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->withOffer()->create();

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Hired,
        ], $user->id);

        expect(fn () => $application->current_step->handle($data))->toThrow(InvalidTransitionException::class);
    });

    it('offer fields are updated when processStep re-runs with new offer data', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->withOffer()->create();
        $stage = Stage::factory()->create(['job_requisition_id' => $application->requisition_id]);

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::OfferAccepted,
            'to_stage_id' => $stage->id,
            'offer_amount' => '95000',
        ], $user->id);

        $application->current_step->handle($data);

        expect((float) $application->fresh()->offer_amount)->toBe(95000.0);
    });
});
