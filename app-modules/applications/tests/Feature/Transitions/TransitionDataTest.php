<?php

declare(strict_types=1);

use Carbon\CarbonInterface;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
use He4rt\Applications\Services\Transitions\TransitionData;
use He4rt\Users\User;
use Illuminate\Support\Facades\Date;

describe('TransitionData::fromArray()', function (): void {
    it('carries the provided rejected_at date', function (): void {
        $rejectedAt = Date::parse('2026-01-15 10:00:00');

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Rejected,
            'rejected_at' => $rejectedAt,
        ], 'user-id');

        expect($data->rejectedAt)->toBe($rejectedAt);
    });

    it('carries the provided offer_extended_at date', function (): void {
        $offerExtendedAt = Date::parse('2026-03-01 09:00:00');

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::OfferExtended,
            'offer_extended_at' => $offerExtendedAt,
        ], 'user-id');

        expect($data->offerExtendedAt)->toBe($offerExtendedAt);
    });

    it('carries the provided offer_response_deadline date', function (): void {
        $deadline = Date::parse('2026-03-08 00:00:00');

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::OfferExtended,
            'offer_response_deadline' => $deadline,
        ], 'user-id');

        expect($data->offerResponseDeadline)->toBe($deadline);
    });

    it('accepts CarbonInterface directly without re-parsing', function (): void {
        $carbon = now()->setDateTimeFrom('2026-01-15 10:00:00');

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Rejected,
            'rejected_at' => $carbon,
        ], 'user-id');

        expect($data->rejectedAt)->toBe($carbon)
            ->and($data->rejectedAt)->toBeInstanceOf(CarbonInterface::class);
    });

    it('accepts status as alias for to_status', function (): void {
        $data = TransitionData::fromArray([
            'status' => ApplicationStatusEnum::InReview,
        ], 'user-id');

        expect($data->toStatus)->toBe(ApplicationStatusEnum::InReview);
    });

    it('prefers to_status over status when both are present', function (): void {
        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Hired,
            'status' => ApplicationStatusEnum::Rejected,
        ], 'user-id');

        expect($data->toStatus)->toBe(ApplicationStatusEnum::Hired);
    });

    it('carries the offer amount as a float', function (): void {
        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::OfferExtended,
            'offer_amount' => 85000.0,
        ], 'user-id');

        expect($data->offerAmount)->toBe(85000.0);
    });

    it('widens an integer offer amount to float', function (): void {
        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::OfferExtended,
            'offer_amount' => 120000,
        ], 'user-id');

        expect($data->offerAmount)->toBe(120000.0);
    });

    it('carries the rejection reason category enum', function (): void {
        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Rejected,
            'rejection_reason_category' => RejectionReasonCategoryEnum::Qualifications,
        ], 'user-id');

        expect($data->rejectionReasonCategory)->toBe(RejectionReasonCategoryEnum::Qualifications);
    });

    it('returns null for all optional fields when not provided', function (): void {
        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::InReview,
        ], 'user-id');

        expect($data->toStageId)->toBeNull()
            ->and($data->advanceStage)->toBeNull()
            ->and($data->rejectionReasonCategory)->toBeNull()
            ->and($data->rejectionReasonDetails)->toBeNull()
            ->and($data->rejectedAt)->toBeNull()
            ->and($data->offerExtendedAt)->toBeNull()
            ->and($data->offerAmount)->toBeNull()
            ->and($data->offerResponseDeadline)->toBeNull()
            ->and($data->notes)->toBeNull();
    });

    it('stores byUserId correctly', function (): void {
        $user = User::factory()->create();

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::InReview,
        ], $user->id);

        expect($data->byUserId)->toBe($user->id);
    });
});

describe('TransitionData::toArray()', function (): void {
    it('serializes dates as datetime strings', function (): void {
        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Rejected,
            'rejected_at' => Date::parse('2026-01-15 10:00:00'),
            'offer_extended_at' => Date::parse('2026-02-01 12:00:00'),
            'offer_response_deadline' => Date::parse('2026-02-08 00:00:00'),
        ], 'user-id');

        $array = $data->toArray();

        expect($array['rejected_at'])->toBe('2026-01-15 10:00:00')
            ->and($array['offer_extended_at'])->toBe('2026-02-01 12:00:00')
            ->and($array['offer_response_deadline'])->toBe('2026-02-08 00:00:00');
    });

    it('serializes enum values as strings', function (): void {
        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Rejected,
            'rejection_reason_category' => RejectionReasonCategoryEnum::Experience,
        ], 'user-id');

        $array = $data->toArray();

        expect($array['to_status'])->toBe('rejected')
            ->and($array['rejection_reason_category'])->toBe('experience');
    });

    it('serializes null fields as null', function (): void {
        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::InReview,
        ], 'user-id');

        $array = $data->toArray();

        expect($array['rejected_at'])->toBeNull()
            ->and($array['offer_amount'])->toBeNull()
            ->and($array['rejection_reason_category'])->toBeNull();
    });

    it('includes by_user_id in serialized array', function (): void {
        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::InReview,
        ], 'some-user-uuid');

        expect($data->toArray()['by_user_id'])->toBe('some-user-uuid');
    });
});

describe('TransitionData helper methods', function (): void {
    it('isStageOnlyChange() is true when to_stage_id is provided', function (): void {
        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::InProgress,
            'to_stage_id' => 'some-stage-uuid',
        ], 'user-id');

        expect($data->isStageOnlyChange())->toBeTrue();
    });

    it('isStageOnlyChange() is true when advance_stage is true', function (): void {
        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::InProgress,
            'advance_stage' => true,
        ], 'user-id');

        expect($data->isStageOnlyChange())->toBeTrue();
    });

    it('isStageOnlyChange() is false when neither to_stage_id nor advance_stage', function (): void {
        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::InProgress,
        ], 'user-id');

        expect($data->isStageOnlyChange())->toBeFalse();
    });

    it('isRejection() returns true for Rejected status', function (): void {
        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Rejected,
        ], 'user-id');

        expect($data->isRejection())->toBeTrue();
    });

    it('isRejection() returns false for non-Rejected status', function (): void {
        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::InReview,
        ], 'user-id');

        expect($data->isRejection())->toBeFalse();
    });

    it('isOfferExtension() returns true for OfferExtended status', function (): void {
        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::OfferExtended,
        ], 'user-id');

        expect($data->isOfferExtension())->toBeTrue();
    });

    it('isWithdrawal() returns true for Withdrawn status', function (): void {
        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Withdrawn,
        ], 'user-id');

        expect($data->isWithdrawal())->toBeTrue();
    });
});
