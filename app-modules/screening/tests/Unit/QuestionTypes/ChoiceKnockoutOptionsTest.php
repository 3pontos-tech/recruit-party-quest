<?php

declare(strict_types=1);

use He4rt\Screening\QuestionTypes\MultipleChoiceType;
use He4rt\Screening\QuestionTypes\Settings\MultipleChoiceSettings;
use He4rt\Screening\QuestionTypes\Settings\SingleChoiceSettings;
use He4rt\Screening\QuestionTypes\SingleChoiceType;

describe('SingleChoiceType::acceptedOptions', function (): void {
    it('reproduces the reported scenario: live admin state has only label, keys stay distinct', function (): void {
        // Exactly the screenshot: recruiter typed two options, no separate value.
        $choices = [
            ['label' => 'opção - 1'],
            ['label' => 'opção - 2'],
        ];

        $options = SingleChoiceType::acceptedOptions($choices);

        expect($options)->toBe([
            'opção - 1' => 'opção - 1',
            'opção - 2' => 'opção - 2',
        ])
            ->and(array_keys($options))->toHaveCount(2); // distinct keys → no toggle-all
    });

    it('prefers an explicit value when present (persisted/normalized state)', function (): void {
        $choices = [
            ['value' => 'a', 'label' => 'Option A'],
            ['value' => 'b', 'label' => 'Option B'],
        ];

        expect(SingleChoiceType::acceptedOptions($choices))->toBe([
            'a' => 'Option A',
            'b' => 'Option B',
        ]);
    });

    it('skips blank and non-array entries', function (): void {
        $choices = [
            ['label' => 'Keep'],
            ['label' => '   '],
            ['value' => '', 'label' => ''],
            'not-an-array',
        ];

        expect(SingleChoiceType::acceptedOptions($choices))->toBe(['Keep' => 'Keep']);
    });

    it('returns an empty array for non-array input', function (): void {
        expect(SingleChoiceType::acceptedOptions(null))->toBe([])
            ->and(SingleChoiceType::acceptedOptions('x'))->toBe([]);
    });
});

describe('MultipleChoiceType::acceptedOptions', function (): void {
    it('keeps keys distinct when live state has only label', function (): void {
        $options = MultipleChoiceType::acceptedOptions([
            ['label' => 'opção - 1'],
            ['label' => 'opção - 2'],
        ]);

        expect($options)->toBe([
            'opção - 1' => 'opção - 1',
            'opção - 2' => 'opção - 2',
        ]);
    });
});

describe('Choice settings normalize value from label deterministically', function (): void {
    it('SingleChoiceSettings fills value from label and drops blanks', function (): void {
        $settings = SingleChoiceSettings::fromArray([
            'layout' => 'radio',
            'choices' => [
                ['label' => 'Yes'],
                ['value' => 'maybe', 'label' => 'Maybe'],
                ['label' => '   '],
            ],
        ]);

        expect($settings->choices)->toBe([
            ['value' => 'Yes', 'label' => 'Yes'],
            ['value' => 'maybe', 'label' => 'Maybe'],
        ]);
    });

    it('MultipleChoiceSettings fills value from label and drops blanks', function (): void {
        $settings = MultipleChoiceSettings::fromArray([
            'choices' => [
                ['label' => 'React'],
                ['value' => '', 'label' => ''],
            ],
        ]);

        expect($settings->choices)->toBe([
            ['value' => 'React', 'label' => 'React'],
        ]);
    });
});
