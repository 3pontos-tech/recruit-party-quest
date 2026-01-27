<?php

declare(strict_types=1);

namespace He4rt\Screening\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MultipleChoiceRule implements ValidationRule
{
    public function __construct(
        private readonly int $min = 0,
        private readonly ?int $max = null
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value)) {
            $fail('Resposta inválida.');

            return;
        }

        $answered = collect($value)
            ->filter(fn ($v) => $v !== null)
            ->count();

        if ($answered < $this->min) {
            $fail(__('screening::question_validations.min-options', ['min' => $this->min]));

            return;
        }

        if ($this->max !== null && $answered > $this->max) {
            $fail(__('screening::question_validations.max-options', ['max' => $this->max]));
        }
    }
}
