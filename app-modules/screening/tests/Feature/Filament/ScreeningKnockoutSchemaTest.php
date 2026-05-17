<?php

declare(strict_types=1);

use He4rt\Screening\Enums\QuestionTypeEnum;
use He4rt\Screening\QuestionTypes\MultipleChoiceType;
use He4rt\Screening\QuestionTypes\NumberType;
use He4rt\Screening\QuestionTypes\QuestionTypeRegistry;
use He4rt\Screening\QuestionTypes\SingleChoiceType;
use He4rt\Screening\QuestionTypes\TextType;
use He4rt\Screening\QuestionTypes\YesNoType;

it('every registered question type exposes a knockout criteria schema via the registry', function (): void {
    foreach (QuestionTypeEnum::cases() as $type) {
        $class = QuestionTypeRegistry::get($type);

        expect($class::knockoutCriteriaSchema())->toBeArray();
    }
});

it('YesNoType exposes a single typed knockout_criteria.expected field', function (): void {
    $schema = YesNoType::knockoutCriteriaSchema();

    expect($schema)->toHaveCount(1)
        ->and($schema[0]->getName())->toBe('knockout_criteria.expected');
});

it('NumberType exposes operator and value fields', function (): void {
    $names = collect(NumberType::knockoutCriteriaSchema())->map(fn ($c): string => $c->getName());

    expect($names->all())->toBe(['knockout_criteria.operator', 'knockout_criteria.value']);
});

it('choice types expose a knockout_criteria.accepted field', function (): void {
    expect(SingleChoiceType::knockoutCriteriaSchema()[0]->getName())->toBe('knockout_criteria.accepted')
        ->and(MultipleChoiceType::knockoutCriteriaSchema()[0]->getName())->toBe('knockout_criteria.accepted');
});

it('TextType has no knockout criteria schema', function (): void {
    expect(TextType::knockoutCriteriaSchema())->toBe([]);
});
