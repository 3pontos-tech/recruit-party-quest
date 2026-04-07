<?php

declare(strict_types=1);

use He4rt\Screening\Enums\QuestionTypeEnum;
use He4rt\Screening\QuestionTypes\NumberType;
use He4rt\Screening\QuestionTypes\Settings\NumberSettings;

describe('NumberType', function (): void {
    it('returns the Number enum case', function (): void {
        expect(NumberType::type())->toBe(QuestionTypeEnum::Number);
    });

    it('returns NumberSettings class name', function (): void {
        expect(NumberType::settingsClass())->toBe(NumberSettings::class);
    });

    it('returns a NumberSettings instance from defaultSettings()', function (): void {
        expect(NumberType::defaultSettings())->toBeInstanceOf(NumberSettings::class);
    });

    it('returns the correct blade component path', function (): void {
        expect(NumberType::component())->toBe('screening::questions.number');
    });

    it('returns a non-empty settings schema with 5 fields', function (): void {
        expect(NumberType::settingsSchema())->toHaveCount(5);
    });

    it('returns a non-empty label string', function (): void {
        expect(NumberType::label())->toBeString()->not->toBeEmpty();
    });

    it('returns a non-empty icon string', function (): void {
        expect(NumberType::icon())->toBeString()->not->toBeEmpty();
    });
});
