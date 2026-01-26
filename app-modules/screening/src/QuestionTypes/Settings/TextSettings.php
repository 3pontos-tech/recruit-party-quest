<?php

declare(strict_types=1);

namespace He4rt\Screening\QuestionTypes\Settings;

use He4rt\Screening\Contracts\HasValidations;

/**
 * Settings for Text question type.
 */
readonly class TextSettings implements HasValidations
{
    public function __construct(
        public ?int $maxLength = null,
        public bool $multiline = false,
        public ?string $placeholder = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            maxLength: isset($data['max_length']) ? (int) $data['max_length'] : null,
            multiline: (bool) ($data['multiline'] ?? false),
            placeholder: $data['placeholder'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'max_length' => $this->maxLength,
            'multiline' => $this->multiline,
            'placeholder' => $this->placeholder,
        ];
    }

    public function rules(string $attribute, bool $required): array
    {
        $rules = [];
        if ($required) {
            $rules[] = 'required';
        }

        if ($this->maxLength !== null) {
            $rules[] = 'max: '.$this->maxLength;
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

        ];
    }
}
