<?php

declare(strict_types=1);

use He4rt\Screening\QuestionTypes\Settings\YesNoSettings;

describe('YesNoSettings', function (): void {
    describe('fromArray()', function (): void {
        it('creates an instance regardless of input data', function (): void {
            expect(YesNoSettings::fromArray([]))->toBeInstanceOf(YesNoSettings::class);
            expect(YesNoSettings::fromArray(['irrelevant' => 'data']))->toBeInstanceOf(YesNoSettings::class);
        });
    });

    describe('toArray()', function (): void {
        it('returns an empty array (yes/no has no extra settings)', function (): void {
            expect(new YesNoSettings()->toArray())->toBe([]);
        });
    });

    describe('rules()', function (): void {
        it('includes required rule when required is true', function (): void {
            $settings = new YesNoSettings();

            expect($settings->rules('answer', true))->toContain('required');
        });

        it('returns empty rules when required is false', function (): void {
            $settings = new YesNoSettings();

            expect($settings->rules('answer', false))->toBe([]);
        });
    });

    describe('initialValue()', function (): void {
        it('returns null', function (): void {
            expect(new YesNoSettings()->initialValue())->toBeNull();
        });
    });

    describe('messages()', function (): void {
        it('returns array with required message key', function (): void {
            $messages = new YesNoSettings()->messages('answer');

            expect($messages)->toHaveKey('answer.required');
        });

        it('uses the provided attribute name in the key', function (): void {
            $messages = new YesNoSettings()->messages('custom_field');

            expect($messages)->toHaveKey('custom_field.required');
        });
    });
});
