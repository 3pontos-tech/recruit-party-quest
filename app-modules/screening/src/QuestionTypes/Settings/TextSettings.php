<?php

declare(strict_types=1);

namespace He4rt\Screening\QuestionTypes\Settings;

/**
 * Settings for Text question type.
 */
readonly class TextSettings
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
}
