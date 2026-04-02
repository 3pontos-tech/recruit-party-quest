<?php

declare(strict_types=1);

use He4rt\Screening\Enums\QuestionTypeEnum;
use He4rt\Screening\QuestionTypes\Settings\SingleChoiceSettings;
use He4rt\Screening\QuestionTypes\SingleChoiceType;

describe('SingleChoiceType', function (): void {
    it('returns the SingleChoice enum case', function (): void {
        expect(SingleChoiceType::type())->toBe(QuestionTypeEnum::SingleChoice);
    });

    it('returns SingleChoiceSettings class name', function (): void {
        expect(SingleChoiceType::settingsClass())->toBe(SingleChoiceSettings::class);
    });

    it('returns a SingleChoiceSettings instance from defaultSettings()', function (): void {
        expect(SingleChoiceType::defaultSettings())->toBeInstanceOf(SingleChoiceSettings::class);
    });

    it('returns the correct blade component path', function (): void {
        expect(SingleChoiceType::component())->toBe('screening::questions.single-choice');
    });

    it('returns a non-empty settings schema with 2 fields', function (): void {
        expect(SingleChoiceType::settingsSchema())->toHaveCount(2);
    });

    it('returns a non-empty label string', function (): void {
        expect(SingleChoiceType::label())->toBeString()->not->toBeEmpty();
    });

    it('returns a non-empty icon string', function (): void {
        expect(SingleChoiceType::icon())->toBeString()->not->toBeEmpty();
    });
});
