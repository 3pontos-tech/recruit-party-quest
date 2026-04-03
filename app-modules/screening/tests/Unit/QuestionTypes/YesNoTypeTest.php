<?php

declare(strict_types=1);

use He4rt\Screening\Enums\QuestionTypeEnum;
use He4rt\Screening\QuestionTypes\Settings\YesNoSettings;
use He4rt\Screening\QuestionTypes\YesNoType;

describe('YesNoType', function (): void {
    it('returns the YesNo enum case', function (): void {
        expect(YesNoType::type())->toBe(QuestionTypeEnum::YesNo);
    });

    it('returns YesNoSettings class name', function (): void {
        expect(YesNoType::settingsClass())->toBe(YesNoSettings::class);
    });

    it('returns a YesNoSettings instance from defaultSettings()', function (): void {
        expect(YesNoType::defaultSettings())->toBeInstanceOf(YesNoSettings::class);
    });

    it('returns the correct blade component path', function (): void {
        expect(YesNoType::component())->toBe('screening::questions.yes-no');
    });

    it('returns an empty settings schema', function (): void {
        expect(YesNoType::settingsSchema())->toBe([]);
    });

    it('returns a non-empty label string', function (): void {
        expect(YesNoType::label())->toBeString()->not->toBeEmpty();
    });

    it('returns a non-empty icon string', function (): void {
        expect(YesNoType::icon())->toBeString()->not->toBeEmpty();
    });
});
