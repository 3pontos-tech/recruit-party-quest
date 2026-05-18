<?php

declare(strict_types=1);

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
