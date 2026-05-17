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
            choices: self::normalizeChoices($data['choices'] ?? []),
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
        return [
            'present',
            'array',
            new MultipleChoiceRule(
                min: $required ? max($this->minSelections, 1) : $this->minSelections,
                max: $this->maxSelections,
            ),
        ];
    }

    public function messages(string $attribute): array
    {
        // The others the rule already send the message
        return [
            $attribute.'.required' => __('screening::question_validations.required'),
        ];
    }

    /**
     * @return array<int, string>
     */
    public function initialValue(): array
    {
        return [];
    }

    /**
     * Guarantee every choice carries a non-empty value mirroring its label.
     *
     * The admin form only asks the recruiter for a single "Option" text
     * (stored as label); value is derived from it deterministically on every
     * read, so the candidate-facing checkboxes and knockout evaluation stay
     * consistent.
     *
     * @param  array<int|string, mixed>  $choices
     * @return array<int, Choice>
     */
    private static function normalizeChoices(array $choices): array
    {
        $normalized = [];

        foreach ($choices as $choice) {
            if (! is_array($choice)) {
                continue;
            }

            $label = mb_trim((string) ($choice['label'] ?? ''));
            $value = mb_trim((string) ($choice['value'] ?? ''));

            $value = $value !== '' ? $value : $label;
            $label = $label !== '' ? $label : $value;

            if ($value === '') {
                continue;
            }

            $normalized[] = ['value' => $value, 'label' => $label];
        }

        return $normalized;
    }
}
