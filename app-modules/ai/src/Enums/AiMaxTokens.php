<?php

declare(strict_types=1);

namespace He4rt\Ai\Enums;

use Filament\Support\Contracts\HasLabel;

enum AiMaxTokens: string implements HasLabel
{
    case Short = 'short';

    case Medium = 'medium';

    case Long = 'long';

    public static function parse(string|self|null $value): ?self
    {
        if ($value instanceof self) {
            return $value;
        }

        return self::tryFrom($value);
    }

    public function getLabel(): ?string
    {
        return $this->name;
    }

    public function getTokens(): int
    {
        return match ($this) {
            self::Short => 500,
            self::Medium => 1000,
            self::Long => 2500,
        };
    }
}
