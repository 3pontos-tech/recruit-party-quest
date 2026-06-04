<?php

declare(strict_types=1);

use Filament\Support\Icons\Heroicon;
use He4rt\Applications\Enums\ApplicationStatusEnum;

it('uses a blocked symbol for a withdrawn application instead of a back arrow', function (): void {
    expect(ApplicationStatusEnum::Withdrawn->getIcon())->toBe(Heroicon::NoSymbol);
});

it('resolves a Heroicon for every status case', function (ApplicationStatusEnum $status): void {
    expect($status->getIcon())->toBeInstanceOf(Heroicon::class);
})->with(ApplicationStatusEnum::cases());

it('flags rejected, withdrawn and declined as terminal states', function (ApplicationStatusEnum $status): void {
    expect($status->isTerminal())->toBeTrue();
})->with([
    ApplicationStatusEnum::Rejected,
    ApplicationStatusEnum::Withdrawn,
    ApplicationStatusEnum::OfferDeclined,
]);

it('does not flag in-progress states as terminal', function (ApplicationStatusEnum $status): void {
    expect($status->isTerminal())->toBeFalse();
})->with([
    ApplicationStatusEnum::New,
    ApplicationStatusEnum::InReview,
    ApplicationStatusEnum::InProgress,
    ApplicationStatusEnum::OfferExtended,
    ApplicationStatusEnum::OfferAccepted,
    ApplicationStatusEnum::Hired,
]);

it('allows rejection only while the process is still open', function (ApplicationStatusEnum $status): void {
    expect($status->allowsRejection())->toBeTrue();
})->with([
    ApplicationStatusEnum::New,
    ApplicationStatusEnum::InReview,
    ApplicationStatusEnum::InProgress,
]);

it('forbids rejection once the process has a final outcome', function (ApplicationStatusEnum $status): void {
    expect($status->allowsRejection())->toBeFalse();
})->with([
    ApplicationStatusEnum::OfferExtended,
    ApplicationStatusEnum::Rejected,
    ApplicationStatusEnum::Withdrawn,
    ApplicationStatusEnum::OfferDeclined,
    ApplicationStatusEnum::OfferAccepted,
    ApplicationStatusEnum::Hired,
]);
