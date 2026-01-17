<?php

declare(strict_types=1);

namespace He4rt\Screening\QuestionTypes\Settings;

/**
 * Settings for Multiple Choice question type.
 *
 * @phpstan-type Choice array{value: string, label: string}
 */
readonly class MultipleChoiceSettings
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
}
