<?php

declare(strict_types=1);

namespace He4rt\Ai\Enums;

use Filament\Support\Contracts\HasLabel;

enum AiResearchReasoningEffort: string implements HasLabel
{
    case High = 'high';

    case Medium = 'medium';

    case Low = 'low';

    public function getLabel(): string
    {
        return $this->name;
    }

    public function getNumberOfSearchQueries(): int
    {
        return match ($this) {
            self::High => 5,
            self::Medium => 3,
            self::Low => 1,
        };
    }
}
