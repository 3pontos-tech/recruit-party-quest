<?php

declare(strict_types=1);

use He4rt\Recruitment\Requisitions\Enums\EmploymentTypeEnum;

it('exposes the five regime cases including intern', function (): void {
    expect(array_map(fn (EmploymentTypeEnum $c): string => $c->value, EmploymentTypeEnum::cases()))
        ->toBe(['clt', 'contractor', 'temporary', 'freelancer', 'intern']);
});

it('no longer recognizes legacy schedule values', function (string $legacy): void {
    expect(EmploymentTypeEnum::tryFrom($legacy))->toBeNull();
})->with(['full_time_employee', 'part_time']);

it('recognizes intern as a valid regime again', function (): void {
    expect(EmploymentTypeEnum::tryFrom('intern'))->toBe(EmploymentTypeEnum::Intern);
});

it('resolves label, color and icon for every case', function (EmploymentTypeEnum $case): void {
    expect($case->getLabel())->toBeString()->not->toBeEmpty()
        ->and($case->getColor())->not->toBeEmpty()
        ->and($case->getIcon())->not->toBeNull();
})->with(EmploymentTypeEnum::cases());
