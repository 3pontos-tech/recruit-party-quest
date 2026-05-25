<?php

declare(strict_types=1);

use He4rt\Applications\Enums\RejectionReasonCategoryEnum;

it('has a ScreeningKnockout case', function (): void {
    expect(RejectionReasonCategoryEnum::ScreeningKnockout->value)->toBe('screening_knockout');
});

it('resolves a non-empty label for ScreeningKnockout', function (): void {
    expect(RejectionReasonCategoryEnum::ScreeningKnockout->getLabel())
        ->toBeString()
        ->not->toBeEmpty()
        ->not->toContain('rejection_reason_category');
});
