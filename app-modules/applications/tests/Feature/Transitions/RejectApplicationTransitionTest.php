<?php

declare(strict_types=1);

use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
use He4rt\Applications\Exceptions\MissingTransitionDataException;
use He4rt\Applications\Models\Application;
use He4rt\Applications\Services\Transitions\RejectApplicationTransition;
use He4rt\Applications\Services\Transitions\TransitionData;
use He4rt\Users\User;
use Illuminate\Support\Facades\Date;

describe('RejectApplicationTransition', function (): void {
    it('canChange() returns false (terminal state)', function (): void {
        $application = Application::factory()->rejected()->create();
        $transition = new RejectApplicationTransition($application);

        expect($transition->canChange())->toBeFalse();
    });

    it('choices() returns empty array (terminal state)', function (): void {
        $application = Application::factory()->rejected()->create();
        $transition = new RejectApplicationTransition($application);

        expect($transition->choices())->toBe([]);
    });

    it('rejects application writing all fields to DB', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->create([
            'status' => ApplicationStatusEnum::Rejected,
        ]);

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Rejected,
            'rejection_reason_category' => RejectionReasonCategoryEnum::Compensation,
            'rejection_reason_details' => 'Salary expectations too high',
            'rejected_at' => Date::parse('2026-02-10 14:00:00'),
        ], $user->id);

        $application->current_step->handle($data);

        $application->refresh();

        expect($application->status)->toBe(ApplicationStatusEnum::Rejected)
            ->and($application->rejected_by)->toBe($user->id)
            ->and($application->rejection_reason_category)->toBe(RejectionReasonCategoryEnum::Compensation)
            ->and($application->rejection_reason_details)->toBe('Salary expectations too high');
    });

    it('throws MissingTransitionDataException when rejection_reason_category is absent', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->create([
            'status' => ApplicationStatusEnum::Rejected,
        ]);

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Rejected,
            // no rejection_reason_category
        ], $user->id);

        expect(fn () => $application->current_step->handle($data))
            ->toThrow(MissingTransitionDataException::class);
    });

    it('uses provided rejected_at when given', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->create([
            'status' => ApplicationStatusEnum::Rejected,
        ]);

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Rejected,
            'rejection_reason_category' => RejectionReasonCategoryEnum::Location,
            'rejected_at' => Date::parse('2026-01-20 08:30:00'),
        ], $user->id);

        $application->current_step->handle($data);

        expect($application->fresh()->rejected_at->toDateTimeString())->toBe('2026-01-20 08:30:00');
    });

    it('defaults rejected_at to now() when not provided', function (): void {
        $user = User::factory()->create();
        $application = Application::factory()->create([
            'status' => ApplicationStatusEnum::Rejected,
        ]);
        $before = now()->subSecond();

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Rejected,
            'rejection_reason_category' => RejectionReasonCategoryEnum::Other,
        ], $user->id);

        $application->current_step->handle($data);

        expect($application->fresh()->rejected_at->isAfter($before))->toBeTrue();
    });
});
