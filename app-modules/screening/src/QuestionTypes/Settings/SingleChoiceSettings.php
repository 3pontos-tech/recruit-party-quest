<?php

declare(strict_types=1);

namespace He4rt\Screening\QuestionTypes\Settings;

use He4rt\Screening\Contracts\HasValidations;

/**
 * Settings for Single Choice question type.
 *
 * @phpstan-type Choice array{value: string, label: string}
 */
readonly class SingleChoiceSettings implements HasValidations
{
    /**
     * @param  array<int, Choice>  $choices
     */
    public function __construct(
        public string $layout = 'radio',
        public array $choices = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            layout: $data['layout'] ?? 'radio',
            choices: self::normalizeChoices($data['choices'] ?? []),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'layout' => $this->layout,
            'choices' => $this->choices,
        ];
    }

    public function rules(string $attribute, bool $required): array
    {
        $rules = [];

        if ($required) {
            $rules[] = 'required';
        }

        return $rules;
    }

    public function initialValue(): ?string
    {
        return null;
    }

    public function messages(string $attribute): array
    {
        return [
            $attribute.'.required' => __('screening::question_validations.required'),
        ];
    }

    /**
     * Guarantee every choice carries a non-empty value mirroring its label.
     *
     * The admin form only asks the recruiter for a single "Option" text
     * (stored as label). The candidate-facing radio and the knockout
     * evaluation rely on `value`, so it is derived from the label here,
     * deterministically on every read.
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
