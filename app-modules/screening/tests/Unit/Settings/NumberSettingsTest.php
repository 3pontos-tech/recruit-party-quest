<?php

declare(strict_types=1);

use He4rt\Screening\QuestionTypes\Settings\NumberSettings;

describe('NumberSettings', function (): void {
    describe('fromArray()', function (): void {
        it('creates with all nulls when array is empty', function (): void {
            $settings = NumberSettings::fromArray([]);

            expect($settings->min)->toBeNull()
                ->and($settings->max)->toBeNull()
                ->and($settings->step)->toBeNull()
                ->and($settings->prefix)->toBeNull()
                ->and($settings->suffix)->toBeNull();
        });

        it('casts numeric string values to float', function (): void {
            $settings = NumberSettings::fromArray([
                'min' => '5',
                'max' => '100',
                'step' => '0.5',
            ]);

            expect($settings->min)->toBe(5.0)
                ->and($settings->max)->toBe(100.0)
                ->and($settings->step)->toBe(0.5);
        });

        it('reads prefix and suffix as strings', function (): void {
            $settings = NumberSettings::fromArray([
                'prefix' => 'R$',
                'suffix' => 'anos',
            ]);

            expect($settings->prefix)->toBe('R$')
                ->and($settings->suffix)->toBe('anos');
        });
    });

    describe('toArray()', function (): void {
        it('serializes all properties', function (): void {
            $settings = new NumberSettings(min: 0.0, max: 100.0, step: 1.0, prefix: '$', suffix: 'yr');

            expect($settings->toArray())->toBe([
                'min' => 0.0,
                'max' => 100.0,
                'step' => 1.0,
                'prefix' => '$',
                'suffix' => 'yr',
            ]);
        });
    });

    describe('rules()', function (): void {
        it('always includes numeric rule', function (): void {
            $settings = new NumberSettings();

            expect($settings->rules('answer', false))->toContain('numeric');
        });

        it('includes required rule when required is true', function (): void {
            $settings = new NumberSettings();

            expect($settings->rules('answer', true))->toContain('required');
        });

        it('includes nullable rule when required is false', function (): void {
            $settings = new NumberSettings();

            expect($settings->rules('answer', false))->toContain('nullable');
        });

        it('defaults to min:0 when min is not set', function (): void {
            $settings = new NumberSettings();

            expect($settings->rules('answer', false))->toContain('min:0');
        });

        it('uses explicit min when set and does not add min:0', function (): void {
            $settings = new NumberSettings(min: 10.0);
            $rules = $settings->rules('answer', false);

            expect($rules)->toContain('min:10')
                ->and($rules)->not->toContain('min:0');
        });

        it('includes max rule when max is set', function (): void {
            $settings = new NumberSettings(max: 500.0);

            expect($settings->rules('answer', false))->toContain('max:500');
        });

        it('does not include max rule when max is not set', function (): void {
            $settings = new NumberSettings();
            $rules = $settings->rules('answer', false);

            $hasMaxRule = collect($rules)->contains(fn ($r) => str_starts_with((string) $r, 'max:'));

            expect($hasMaxRule)->toBeFalse();
        });

        it('combines all constraints when fully configured', function (): void {
            $settings = new NumberSettings(min: 5.0, max: 50.0);
            $rules = $settings->rules('answer', true);

            expect($rules)->toContain('numeric')
                ->and($rules)->toContain('required')
                ->and($rules)->toContain('min:5')
                ->and($rules)->toContain('max:50');
        });
    });

    describe('initialValue()', function (): void {
        it('returns null', function (): void {
            expect(new NumberSettings()->initialValue())->toBeNull();
        });
    });

    describe('messages()', function (): void {
        it('returns array with expected message keys', function (): void {
            $messages = new NumberSettings()->messages('salary');

            expect($messages)->toHaveKey('salary.min')
                ->and($messages)->toHaveKey('salary.max')
                ->and($messages)->toHaveKey('salary.required')
                ->and($messages)->toHaveKey('salary.numeric');
        });

        it('uses the provided attribute name in all keys', function (): void {
            $messages = new NumberSettings()->messages('experience_years');

            expect($messages)->toHaveKey('experience_years.min');
        });
    });
});
