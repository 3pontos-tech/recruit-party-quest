<?php

declare(strict_types=1);

namespace He4rt\Screening\QuestionTypes\Settings;

/**
 * Settings for Single Choice question type.
 *
 * @phpstan-type Choice array{value: string, label: string}
 */
readonly class SingleChoiceSettings
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
}
