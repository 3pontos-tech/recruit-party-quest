<?php

declare(strict_types=1);

use He4rt\Screening\Enums\QuestionTypeEnum;
use He4rt\Screening\QuestionTypes\Settings\TextSettings;
use He4rt\Screening\QuestionTypes\TextType;

describe('TextType', function (): void {
    it('returns the Text enum case', function (): void {
        expect(TextType::type())->toBe(QuestionTypeEnum::Text);
    });

    it('returns TextSettings class name', function (): void {
        expect(TextType::settingsClass())->toBe(TextSettings::class);
    });

    it('returns a TextSettings instance from defaultSettings()', function (): void {
        expect(TextType::defaultSettings())->toBeInstanceOf(TextSettings::class);
    });

    it('returns the correct blade component path', function (): void {
        expect(TextType::component())->toBe('screening::questions.text');
    });

    it('returns a non-empty settings schema with 3 fields', function (): void {
        expect(TextType::settingsSchema())->toHaveCount(3);
    });

    it('returns a non-empty label string', function (): void {
        expect(TextType::label())->toBeString()->not->toBeEmpty();
    });

    it('returns a non-empty icon string', function (): void {
        expect(TextType::icon())->toBeString()->not->toBeEmpty();
    });
});
