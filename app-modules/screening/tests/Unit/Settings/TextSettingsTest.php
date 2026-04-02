<?php

declare(strict_types=1);

use He4rt\Screening\QuestionTypes\Settings\TextSettings;

describe('TextSettings', function (): void {
    describe('fromArray()', function (): void {
        it('creates with default values when array is empty', function (): void {
            $settings = TextSettings::fromArray([]);

            expect($settings->maxLength)->toBeNull()
                ->and($settings->multiline)->toBeFalse()
                ->and($settings->placeholder)->toBeNull();
        });

        it('casts max_length to int', function (): void {
            $settings = TextSettings::fromArray(['max_length' => '500']);

            expect($settings->maxLength)->toBe(500);
        });

        it('reads multiline as boolean', function (): void {
            $settings = TextSettings::fromArray(['multiline' => true]);

            expect($settings->multiline)->toBeTrue();
        });

        it('reads placeholder as string', function (): void {
            $settings = TextSettings::fromArray(['placeholder' => 'Digite aqui...']);

            expect($settings->placeholder)->toBe('Digite aqui...');
        });

        it('creates from array with all fields populated', function (): void {
            $settings = TextSettings::fromArray([
                'max_length' => '250',
                'multiline' => true,
                'placeholder' => 'Descreva sua experiência',
            ]);

            expect($settings->maxLength)->toBe(250)
                ->and($settings->multiline)->toBeTrue()
                ->and($settings->placeholder)->toBe('Descreva sua experiência');
        });
    });

    describe('toArray()', function (): void {
        it('serializes all properties to array', function (): void {
            $settings = new TextSettings(maxLength: 300, multiline: true, placeholder: 'Ex:');

            expect($settings->toArray())->toBe([
                'max_length' => 300,
                'multiline' => true,
                'placeholder' => 'Ex:',
            ]);
        });

        it('serializes null values correctly', function (): void {
            $settings = new TextSettings();

            expect($settings->toArray())->toBe([
                'max_length' => null,
                'multiline' => false,
                'placeholder' => null,
            ]);
        });
    });

    describe('rules()', function (): void {
        it('includes required rule when required is true', function (): void {
            $settings = new TextSettings();

            expect($settings->rules('answer', true))->toContain('required');
        });

        it('does not include required rule when required is false', function (): void {
            $settings = new TextSettings();

            expect($settings->rules('answer', false))->not->toContain('required');
        });

        it('includes max rule when maxLength is set (note: space after colon)', function (): void {
            $settings = new TextSettings(maxLength: 200);

            expect($settings->rules('answer', false))->toContain('max: 200');
        });

        it('does not include max rule when maxLength is null', function (): void {
            $settings = new TextSettings();
            $rules = $settings->rules('answer', false);

            $hasMaxRule = collect($rules)->contains(fn ($r) => str_starts_with((string) $r, 'max:'));

            expect($hasMaxRule)->toBeFalse();
        });

        it('combines required and max rules', function (): void {
            $settings = new TextSettings(maxLength: 100);
            $rules = $settings->rules('answer', true);

            expect($rules)->toContain('required')
                ->and($rules)->toContain('max: 100');
        });
    });

    describe('initialValue()', function (): void {
        it('returns null', function (): void {
            expect(new TextSettings()->initialValue())->toBeNull();
        });
    });

    describe('messages()', function (): void {
        it('returns array with required and max message keys', function (): void {
            $messages = new TextSettings()->messages('answer');

            expect($messages)->toHaveKey('answer.required')
                ->and($messages)->toHaveKey('answer.max');
        });

        it('uses the provided attribute name in all keys', function (): void {
            $messages = new TextSettings()->messages('bio');

            expect($messages)->toHaveKey('bio.required')
                ->and($messages)->toHaveKey('bio.max');
        });
    });
});
