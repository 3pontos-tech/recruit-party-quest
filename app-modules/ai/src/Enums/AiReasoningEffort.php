<?php

declare(strict_types=1);

namespace He4rt\Ai\Enums;

use Filament\Support\Contracts\HasLabel;

enum AiReasoningEffort: string implements HasLabel
{
    case Low = 'low';

    case Medium = 'medium';

    case High = 'high';

    public static function parse(string|self|null $value): ?self
    {
        if ($value instanceof self) {
            return $value;
        }

        return self::tryFrom($value);
    }

    public function getLabel(): string
    {
        return __('ai::filament.enums.reasoning_effort.'.$this->value.'.label');
    }
}
