<?php

declare(strict_types=1);

use He4rt\Screening\QuestionTypes\Settings\SingleChoiceSettings;

describe('SingleChoiceSettings', function (): void {
    describe('fromArray()', function (): void {
        it('defaults layout to radio when not provided', function (): void {
            $settings = SingleChoiceSettings::fromArray([]);

            expect($settings->layout)->toBe('radio');
        });

        it('defaults choices to empty array when not provided', function (): void {
            $settings = SingleChoiceSettings::fromArray([]);

            expect($settings->choices)->toBe([]);
        });

        it('reads layout from array', function (): void {
            $settings = SingleChoiceSettings::fromArray(['layout' => 'dropdown']);

            expect($settings->layout)->toBe('dropdown');
        });

        it('reads choices from array', function (): void {
            $choices = [
                ['value' => 'php', 'label' => 'PHP'],
                ['value' => 'js', 'label' => 'JavaScript'],
            ];

            $settings = SingleChoiceSettings::fromArray(['choices' => $choices]);

            expect($settings->choices)->toBe($choices);
        });
    });

    describe('toArray()', function (): void {
        it('serializes layout and choices', function (): void {
            $choices = [['value' => 'yes', 'label' => 'Yes']];
            $settings = new SingleChoiceSettings(layout: 'dropdown', choices: $choices);

            expect($settings->toArray())->toBe([
                'layout' => 'dropdown',
                'choices' => $choices,
            ]);
        });
    });

    describe('rules()', function (): void {
        it('includes required rule when required is true', function (): void {
            $settings = new SingleChoiceSettings();
            $rules = $settings->rules('answer', true);

            expect(array_values($rules))->toContain('required');
        });

        it('returns empty rules when required is false', function (): void {
            $settings = new SingleChoiceSettings();

            expect($settings->rules('answer', false))->toBe([]);
        });
    });

    describe('initialValue()', function (): void {
        it('returns null', function (): void {
            expect(new SingleChoiceSettings()->initialValue())->toBeNull();
        });
    });

    describe('messages()', function (): void {
        it('returns array with required message key', function (): void {
            $messages = new SingleChoiceSettings()->messages('language');

            expect($messages)->toHaveKey('language.required');
        });

        it('uses the provided attribute name in the key', function (): void {
            $messages = new SingleChoiceSettings()->messages('preferred_stack');

            expect($messages)->toHaveKey('preferred_stack.required');
        });
    });
});
