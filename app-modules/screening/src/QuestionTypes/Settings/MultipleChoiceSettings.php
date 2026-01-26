<?php

declare(strict_types=1);

namespace He4rt\Screening\QuestionTypes\Settings;

use He4rt\Screening\Contracts\HasValidations;
use He4rt\Screening\Rules\MultipleChoiceRule;

/**
 * Settings for Multiple Choice question type.
 *
 * @phpstan-type Choice array{value: string, label: string}
 */
readonly class MultipleChoiceSettings implements HasValidations
{
    /**
     * @param  array<int, Choice>  $choices
     */
    public function __construct(
        public int $minSelections = 0,
        public ?int $maxSelections = null,
        public array $choices = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            minSelections: (int) ($data['min_selections'] ?? 0),
            maxSelections: isset($data['max_selections']) ? (int) $data['max_selections'] : null,
            choices: $data['choices'] ?? [],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'min_selections' => $this->minSelections,
            'max_selections' => $this->maxSelections,
            'choices' => $this->choices,
        ];
    }

    public function rules(string $attribute, bool $required): array
    {
        $rules = [
            'array',
            new MultipleChoiceRule(min: $this->minSelections, max: $this->maxSelections),
        ];

        if ($required) {
            array_unshift($rules, 'required');
        }

        return $rules;
    }

    public function messages(string $attribute): array
    {
        return [

        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function initialValue(): array
    {
        $response = [];

        foreach ($this->choices as $choice) {
            $response[$choice['value']] = null;
        }

        return $response;
    }
}
