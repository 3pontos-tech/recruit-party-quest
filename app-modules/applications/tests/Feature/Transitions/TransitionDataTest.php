<?php

declare(strict_types=1);

use Carbon\CarbonInterface;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\RejectionReasonCategoryEnum;
use He4rt\Applications\Services\Transitions\TransitionData;
use He4rt\Users\User;

describe('TransitionData::fromArray()', function (): void {
    it('parses rejected_at string to CarbonInterface', function (): void {
        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Rejected,
            'rejected_at' => '2026-01-15 10:00:00',
        ], 'user-id');

        expect($data->rejectedAt)->toBeInstanceOf(CarbonInterface::class)
            ->and($data->rejectedAt->toDateTimeString())->toBe('2026-01-15 10:00:00');
    });

    it('parses offer_extended_at string to CarbonInterface', function (): void {
        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::OfferExtended,
            'offer_extended_at' => '2026-03-01 09:00:00',
        ], 'user-id');

        expect($data->offerExtendedAt)->toBeInstanceOf(CarbonInterface::class)
            ->and($data->offerExtendedAt->toDateTimeString())->toBe('2026-03-01 09:00:00');
    });

    it('parses offer_response_deadline string to CarbonInterface', function (): void {
        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::OfferExtended,
            'offer_response_deadline' => '2026-03-08 00:00:00',
        ], 'user-id');

        expect($data->offerResponseDeadline)->toBeInstanceOf(CarbonInterface::class)
            ->and($data->offerResponseDeadline->toDateTimeString())->toBe('2026-03-08 00:00:00');
    });

    it('accepts CarbonInterface directly without re-parsing', function (): void {
        $carbon = now()->setDateTimeFrom('2026-01-15 10:00:00');

        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Rejected,
            'rejected_at' => $carbon,
        ], 'user-id');

        expect($data->rejectedAt)->toBe($carbon);
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

    it('casts offer_amount string to float', function (): void {
        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::OfferExtended,
            'offer_amount' => '85000',
        ], 'user-id');

        expect($data->offerAmount)->toBe(85000.0);
    });

    it('casts offer_amount integer to float', function (): void {
        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::OfferExtended,
            'offer_amount' => 120000,
        ], 'user-id');

        expect($data->offerAmount)->toBe(120000.0);
    });

    it('casts rejection_reason_category string value to enum', function (): void {
        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Rejected,
            'rejection_reason_category' => 'qualifications',
        ], 'user-id');

        expect($data->rejectionReasonCategory)->toBe(RejectionReasonCategoryEnum::Qualifications);
    });

    it('returns null for invalid rejection_reason_category value', function (): void {
        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Rejected,
            'rejection_reason_category' => 'invalid_value',
        ], 'user-id');

        expect($data->rejectionReasonCategory)->toBeNull();
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
            'rejected_at' => '2026-01-15 10:00:00',
            'offer_extended_at' => '2026-02-01 12:00:00',
            'offer_response_deadline' => '2026-02-08 00:00:00',
        ], 'user-id');

        $array = $data->toArray();

        expect($array['rejected_at'])->toBe('2026-01-15 10:00:00')
            ->and($array['offer_extended_at'])->toBe('2026-02-01 12:00:00')
            ->and($array['offer_response_deadline'])->toBe('2026-02-08 00:00:00');
    });

    it('serializes enum values as strings', function (): void {
        $data = TransitionData::fromArray([
            'to_status' => ApplicationStatusEnum::Rejected,
            'rejection_reason_category' => 'experience',
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
