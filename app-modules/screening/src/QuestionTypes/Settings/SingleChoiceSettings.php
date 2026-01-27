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
            choices: $data['choices'] ?? [],
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
            $rules['required'] = 'required';
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
}
