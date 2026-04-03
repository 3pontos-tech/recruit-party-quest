<?php

declare(strict_types=1);

use He4rt\Screening\Enums\QuestionTypeEnum;
use He4rt\Screening\QuestionTypes\MultipleChoiceType;
use He4rt\Screening\QuestionTypes\Settings\MultipleChoiceSettings;

describe('MultipleChoiceType', function (): void {
    it('returns the MultipleChoice enum case', function (): void {
        expect(MultipleChoiceType::type())->toBe(QuestionTypeEnum::MultipleChoice);
    });

    it('returns MultipleChoiceSettings class name', function (): void {
        expect(MultipleChoiceType::settingsClass())->toBe(MultipleChoiceSettings::class);
    });

    it('returns a MultipleChoiceSettings instance from defaultSettings()', function (): void {
        expect(MultipleChoiceType::defaultSettings())->toBeInstanceOf(MultipleChoiceSettings::class);
    });

    it('returns the correct blade component path', function (): void {
        expect(MultipleChoiceType::component())->toBe('screening::questions.multiple-choice');
    });

    it('returns a non-empty settings schema with 3 fields', function (): void {
        expect(MultipleChoiceType::settingsSchema())->toHaveCount(3);
    });

    it('returns a non-empty label string', function (): void {
        expect(MultipleChoiceType::label())->toBeString()->not->toBeEmpty();
    });

    it('returns a non-empty icon string', function (): void {
        expect(MultipleChoiceType::icon())->toBeString()->not->toBeEmpty();
    });
});
