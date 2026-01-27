<?php

declare(strict_types=1);

namespace He4rt\Screening\QuestionTypes\Settings;

use He4rt\Screening\Contracts\HasValidations;

/**
 * Settings for Yes/No question type.
 *
 * This type has no additional settings - it's a simple boolean toggle.
 */
readonly class YesNoSettings implements HasValidations
{
    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [];
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
}
