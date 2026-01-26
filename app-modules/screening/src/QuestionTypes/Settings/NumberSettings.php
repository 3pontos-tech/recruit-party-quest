<?php

declare(strict_types=1);

namespace He4rt\Screening\QuestionTypes\Settings;

use He4rt\Screening\Contracts\HasValidations;

/**
 * Settings for Number question type.
 */
readonly class NumberSettings implements HasValidations
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

    public function rules(string $attribute, bool $required): array
    {
        $rules = ['numeric'];

        $rules[] = $required ? 'required' : 'nullable';

        if (is_null($this->min)) {
            $rules[] = 'min:0';
        }

        if ($this->min !== null) {
            $rules[] = 'min:'.$this->min;
        }

        if ($this->max !== null) {
            $rules[] = 'max:'.$this->max;
        }

        return $rules;
    }

    public function initialValue(): null
    {
        return null;
    }

    public function messages(string $attribute): array
    {
        return [
            $attribute.'.min' => __('screening::question_validations.numeric-min', ['min' => $this->min ?? 0]),
            $attribute.'.required' => __('screening::question_validations.required'),
            $attribute.'.numeric' => __('screening::question_validations.numeric'),
            $attribute.'.max' => __('screening::question_validations.numeric-max', ['max' => $this->max ?? 0]),
        ];
    }
}
