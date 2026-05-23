<?php

declare(strict_types=1);

use He4rt\Recruitment\Requisitions\Enums\ExperienceLevelEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkArrangementEnum;
use He4rt\Recruitment\Requisitions\Enums\WorkScheduleEnum;

it('has exactly the four schedule cases', function (): void {
    expect(array_map(fn (WorkScheduleEnum $c): string => $c->value, WorkScheduleEnum::cases()))
        ->toBe(['full_time', 'part_time', 'hourly', 'shift']);
});

it('resolves label, color and icon for every case', function (WorkScheduleEnum $case): void {
    expect($case->getLabel())->toBeString()->not->toBeEmpty()
        ->and($case->getColor())->not->toBeEmpty()
        ->and($case->getIcon())->not->toBeNull();
})->with(WorkScheduleEnum::cases());

it('does not alter WorkArrangement or ExperienceLevel enums', function (): void {
    expect(array_map(fn (WorkArrangementEnum $c) => $c->value, WorkArrangementEnum::cases()))
        ->toBe(['remote', 'hybrid', 'on_site'])
        ->and(ExperienceLevelEnum::tryFrom('trainee'))
        ->not->toBeNull();
});
