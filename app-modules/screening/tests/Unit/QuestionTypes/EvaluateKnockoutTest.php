<?php

declare(strict_types=1);

use He4rt\Screening\QuestionTypes\MultipleChoiceType;
use He4rt\Screening\QuestionTypes\NumberType;
use He4rt\Screening\QuestionTypes\SingleChoiceType;
use He4rt\Screening\QuestionTypes\TextType;
use He4rt\Screening\QuestionTypes\YesNoType;

describe('YesNoType::evaluateKnockout', function (): void {
    it('passes when answer equals expected', function (): void {
        expect(YesNoType::evaluateKnockout(['expected' => 'yes'], 'yes'))->toBeTrue();
    });
    it('fails when answer differs from expected', function (): void {
        expect(YesNoType::evaluateKnockout(['expected' => 'yes'], 'no'))->toBeFalse();
    });
    it('passes (defensive) when criteria is incomplete', function (): void {
        expect(YesNoType::evaluateKnockout([], 'no'))->toBeTrue();
    });
});

describe('NumberType::evaluateKnockout', function (): void {
    it('passes when answer >= threshold', function (): void {
        expect(NumberType::evaluateKnockout(['operator' => '>=', 'value' => 3], '3'))->toBeTrue();
    });
    it('fails when answer < threshold for >=', function (): void {
        expect(NumberType::evaluateKnockout(['operator' => '>=', 'value' => 3], '2'))->toBeFalse();
    });
    it('handles =, >, <, <= operators', function (): void {
        expect(NumberType::evaluateKnockout(['operator' => '=', 'value' => 5], '5'))->toBeTrue();
        expect(NumberType::evaluateKnockout(['operator' => '>', 'value' => 5], '5'))->toBeFalse();
        expect(NumberType::evaluateKnockout(['operator' => '<', 'value' => 5], '4'))->toBeTrue();
        expect(NumberType::evaluateKnockout(['operator' => '<=', 'value' => 5], '5'))->toBeTrue();
    });
    it('passes (defensive) when criteria/value missing or non-numeric', function (): void {
        expect(NumberType::evaluateKnockout(['operator' => '>='], 'abc'))->toBeTrue();
        expect(NumberType::evaluateKnockout(['minimum' => '3'], '2'))->toBeTrue();
    });
    it('treats = with floating-point answers as equal within epsilon', function (): void {
        // 0.1 + 0.2 === 0.30000000000000004 in IEEE-754; strict === would fail.
        expect(NumberType::evaluateKnockout(['operator' => '=', 'value' => 0.3], 0.1 + 0.2))->toBeTrue();
    });
});

describe('SingleChoiceType::evaluateKnockout', function (): void {
    it('passes when answer is in accepted set', function (): void {
        expect(SingleChoiceType::evaluateKnockout(['accepted' => ['python', 'go']], 'go'))->toBeTrue();
    });
    it('fails when answer is not accepted', function (): void {
        expect(SingleChoiceType::evaluateKnockout(['accepted' => ['python']], 'java'))->toBeFalse();
    });
    it('passes (defensive) when accepted list is missing', function (): void {
        expect(SingleChoiceType::evaluateKnockout([], 'java'))->toBeTrue();
    });
});

describe('MultipleChoiceType::evaluateKnockout', function (): void {
    it('passes when at least one selected is accepted', function (): void {
        expect(MultipleChoiceType::evaluateKnockout(['accepted' => ['react', 'vue']], ['vue', 'angular']))->toBeTrue();
    });
    it('fails when none selected is accepted', function (): void {
        expect(MultipleChoiceType::evaluateKnockout(['accepted' => ['react']], ['vue', 'angular']))->toBeFalse();
    });
    it('passes (defensive) when accepted list missing', function (): void {
        expect(MultipleChoiceType::evaluateKnockout([], ['vue']))->toBeTrue();
    });
});

describe('TextType::evaluateKnockout', function (): void {
    it('never knocks out (always passes)', function (): void {
        expect(TextType::evaluateKnockout(['anything' => 'x'], 'whatever'))->toBeTrue();
    });
    it('exposes an empty knockout criteria schema', function (): void {
        expect(TextType::knockoutCriteriaSchema())->toBe([]);
    });
});
