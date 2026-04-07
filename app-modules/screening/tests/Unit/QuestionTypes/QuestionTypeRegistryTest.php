<?php

declare(strict_types=1);

use He4rt\Screening\Enums\QuestionTypeEnum;
use He4rt\Screening\QuestionTypes\MultipleChoiceType;
use He4rt\Screening\QuestionTypes\NumberType;
use He4rt\Screening\QuestionTypes\QuestionTypeRegistry;
use He4rt\Screening\QuestionTypes\SingleChoiceType;
use He4rt\Screening\QuestionTypes\TextType;
use He4rt\Screening\QuestionTypes\YesNoType;

describe('QuestionTypeRegistry', function (): void {
    describe('get()', function (): void {
        it('returns the correct class for each registered type', function (QuestionTypeEnum $enum, string $expectedClass): void {
            expect(QuestionTypeRegistry::get($enum))->toBe($expectedClass);
        })->with([
            'yes_no' => [QuestionTypeEnum::YesNo, YesNoType::class],
            'text' => [QuestionTypeEnum::Text, TextType::class],
            'number' => [QuestionTypeEnum::Number, NumberType::class],
            'single_choice' => [QuestionTypeEnum::SingleChoice, SingleChoiceType::class],
            'multiple_choice' => [QuestionTypeEnum::MultipleChoice, MultipleChoiceType::class],
        ]);
    });

    describe('all()', function (): void {
        it('returns all 5 registered types', function (): void {
            expect(QuestionTypeRegistry::all())->toHaveCount(5);
        });

        it('contains all expected type classes', function (): void {
            $all = QuestionTypeRegistry::all();

            expect($all)->toContain(YesNoType::class)
                ->and($all)->toContain(TextType::class)
                ->and($all)->toContain(NumberType::class)
                ->and($all)->toContain(SingleChoiceType::class)
                ->and($all)->toContain(MultipleChoiceType::class);
        });

        it('returns indexed array values', function (): void {
            $all = QuestionTypeRegistry::all();

            expect(array_is_list($all))->toBeTrue();
        });
    });

    describe('getSettingsSchema()', function (): void {
        it('returns empty array when type is null', function (): void {
            expect(QuestionTypeRegistry::getSettingsSchema(null))->toBe([]);
        });

        it('returns empty array for yes_no type (no extra settings)', function (): void {
            expect(QuestionTypeRegistry::getSettingsSchema(QuestionTypeEnum::YesNo))->toBe([]);
        });

        it('returns non-empty schema for types that have settings', function (QuestionTypeEnum $type): void {
            expect(QuestionTypeRegistry::getSettingsSchema($type))->not->toBeEmpty();
        })->with([
            'text' => QuestionTypeEnum::Text,
            'number' => QuestionTypeEnum::Number,
            'single_choice' => QuestionTypeEnum::SingleChoice,
            'multiple_choice' => QuestionTypeEnum::MultipleChoice,
        ]);
    });
});
