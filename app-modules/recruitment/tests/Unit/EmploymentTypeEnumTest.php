<?php

declare(strict_types=1);

use He4rt\Recruitment\Requisitions\Enums\EmploymentTypeEnum;

it('exposes only the four regime cases', function (): void {
    expect(array_map(fn (EmploymentTypeEnum $c): string => $c->value, EmploymentTypeEnum::cases()))
        ->toBe(['clt', 'contractor', 'temporary', 'freelancer']);
});

it('no longer recognizes legacy schedule/intern values', function (string $legacy): void {
    expect(EmploymentTypeEnum::tryFrom($legacy))->toBeNull();
})->with(['full_time_employee', 'part_time', 'intern']);

it('resolves label, color and icon for every case', function (EmploymentTypeEnum $case): void {
    expect($case->getLabel())->toBeString()->not->toBeEmpty()
        ->and($case->getColor())->not->toBeEmpty()
        ->and($case->getIcon())->not->toBeNull();
})->with(EmploymentTypeEnum::cases());
