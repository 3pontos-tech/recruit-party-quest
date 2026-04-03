<?php

declare(strict_types=1);

use He4rt\Screening\QuestionTypes\Settings\MultipleChoiceSettings;
use He4rt\Screening\Rules\MultipleChoiceRule;

describe('MultipleChoiceSettings', function (): void {
    describe('fromArray()', function (): void {
        it('defaults minSelections to 0 when not provided', function (): void {
            $settings = MultipleChoiceSettings::fromArray([]);

            expect($settings->minSelections)->toBe(0);
        });

        it('defaults maxSelections to null when not provided', function (): void {
            $settings = MultipleChoiceSettings::fromArray([]);

            expect($settings->maxSelections)->toBeNull();
        });

        it('defaults choices to empty array when not provided', function (): void {
            $settings = MultipleChoiceSettings::fromArray([]);

            expect($settings->choices)->toBe([]);
        });

        it('casts min_selections to int', function (): void {
            $settings = MultipleChoiceSettings::fromArray(['min_selections' => '2']);

            expect($settings->minSelections)->toBe(2);
        });

        it('casts max_selections to int', function (): void {
            $settings = MultipleChoiceSettings::fromArray(['max_selections' => '5']);

            expect($settings->maxSelections)->toBe(5);
        });

        it('reads choices from array', function (): void {
            $choices = [
                ['value' => 'php', 'label' => 'PHP'],
                ['value' => 'go', 'label' => 'Go'],
            ];

            $settings = MultipleChoiceSettings::fromArray(['choices' => $choices]);

            expect($settings->choices)->toBe($choices);
        });
    });

    describe('toArray()', function (): void {
        it('serializes all properties', function (): void {
            $choices = [['value' => 'php', 'label' => 'PHP']];
            $settings = new MultipleChoiceSettings(minSelections: 1, maxSelections: 3, choices: $choices);

            expect($settings->toArray())->toBe([
                'min_selections' => 1,
                'max_selections' => 3,
                'choices' => $choices,
            ]);
        });
    });

    describe('rules()', function (): void {
        it('always includes present and array rules', function (): void {
            $settings = new MultipleChoiceSettings();
            $rules = $settings->rules('skills', false);

            expect($rules[0])->toBe('present')
                ->and($rules[1])->toBe('array');
        });

        it('always includes a MultipleChoiceRule instance as third rule', function (): void {
            $settings = new MultipleChoiceSettings();
            $rules = $settings->rules('skills', false);

            expect($rules[2])->toBeInstanceOf(MultipleChoiceRule::class);
        });

        it('when required=true and minSelections=0, enforces at least 1 selection', function (): void {
            $settings = new MultipleChoiceSettings(minSelections: 0);
            /** @var MultipleChoiceRule $rule */
            $rule = $settings->rules('skills', true)[2];

            $failed = false;
            $rule->validate('skills', [], function () use (&$failed): void {
                $failed = true;
            });

            expect($failed)->toBeTrue();
        });

        it('when required=true and minSelections=3, preserves the higher min', function (): void {
            $settings = new MultipleChoiceSettings(minSelections: 3);
            /** @var MultipleChoiceRule $rule */
            $rule = $settings->rules('skills', true)[2];

            $failed = false;
            $rule->validate('skills', ['a', 'b'], function () use (&$failed): void {
                $failed = true;
            });

            expect($failed)->toBeTrue();
        });

        it('when required=false, uses minSelections directly (can be 0)', function (): void {
            $settings = new MultipleChoiceSettings(minSelections: 0);
            /** @var MultipleChoiceRule $rule */
            $rule = $settings->rules('skills', false)[2];

            $failed = false;
            $rule->validate('skills', [], function () use (&$failed): void {
                $failed = true;
            });

            expect($failed)->toBeFalse();
        });

        it('returns exactly 3 rules', function (): void {
            $settings = new MultipleChoiceSettings();

            expect($settings->rules('skills', false))->toHaveCount(3);
        });
    });

    describe('initialValue()', function (): void {
        it('returns an empty array', function (): void {
            expect(new MultipleChoiceSettings()->initialValue())->toBe([]);
        });
    });

    describe('messages()', function (): void {
        it('returns array with required message key', function (): void {
            $messages = new MultipleChoiceSettings()->messages('skills');

            expect($messages)->toHaveKey('skills.required');
        });

        it('uses the provided attribute name in the key', function (): void {
            $messages = new MultipleChoiceSettings()->messages('technologies');

            expect($messages)->toHaveKey('technologies.required');
        });
    });
});
