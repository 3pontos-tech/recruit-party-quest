<?php

declare(strict_types=1);

use He4rt\Applications\Enums\ApplicationListSort;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Enums\ApplicationStatusGroup;
use He4rt\Applications\Enums\ScreeningVerdictFilter;
use He4rt\Applications\Enums\SeenFilter;

it('maps every application status to exactly one group', function (): void {
    foreach (ApplicationStatusEnum::cases() as $status) {
        expect(ApplicationStatusGroup::fromStatus($status))->toBeInstanceOf(ApplicationStatusGroup::class);
    }
});

it('exposes the status values of each group in enum order', function (): void {
    expect(ApplicationStatusGroup::New->values())->toBe(['new', 'in_review'])
        ->and(ApplicationStatusGroup::Active->values())->toBe(['in_progress'])
        ->and(ApplicationStatusGroup::Offer->values())->toBe(['offer_extended', 'offer_accepted', 'hired'])
        ->and(ApplicationStatusGroup::Closed->values())->toBe(['offer_declined', 'rejected', 'withdrawn']);
});

it('has translated labels for every new enum case', function (): void {
    $cases = [
        ...ApplicationStatusGroup::cases(),
        ...ScreeningVerdictFilter::cases(),
        ...SeenFilter::cases(),
        ...ApplicationListSort::cases(),
    ];

    foreach (['en', 'pt_BR'] as $locale) {
        app()->setLocale($locale);

        foreach ($cases as $case) {
            expect($case->getLabel())->not->toContain('::');
        }
    }
});
