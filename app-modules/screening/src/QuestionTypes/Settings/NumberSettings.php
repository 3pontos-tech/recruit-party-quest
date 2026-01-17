<?php

declare(strict_types=1);

namespace He4rt\Screening\QuestionTypes\Settings;

/**
 * Settings for Number question type.
 */
readonly class NumberSettings
{
    public function __construct(
        public ?float $min = null,
        public ?float $max = null,
        public ?float $step = null,
        public ?string $prefix = null,
        public ?string $suffix = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            min: isset($data['min']) ? (float) $data['min'] : null,
            max: isset($data['max']) ? (float) $data['max'] : null,
            step: isset($data['step']) ? (float) $data['step'] : null,
            prefix: $data['prefix'] ?? null,
            suffix: $data['suffix'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'min' => $this->min,
            'max' => $this->max,
            'step' => $this->step,
            'prefix' => $this->prefix,
            'suffix' => $this->suffix,
        ];
    }
}
