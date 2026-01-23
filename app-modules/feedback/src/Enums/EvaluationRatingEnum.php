<?php

declare(strict_types=1);

namespace He4rt\Feedback\Enums;

use App\Enums\Concerns\StringifyEnum;
use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EvaluationRatingEnum: string implements HasColor, HasLabel
{
    use StringifyEnum;

    case StrongNo = 'strong_no';
    case No = 'no';
    case Maybe = 'maybe';
    case Yes = 'yes';
    case StrongYes = 'strong_yes';

    public function getColor(): array
    {
        return match ($this) {
            self::StrongNo => Color::Red,
            self::No => Color::Orange,
            self::Maybe => Color::Yellow,
            self::Yes => Color::Emerald,
            self::StrongYes => Color::Green,
        };
    }

    public function getLabel(): string
    {
        return __('feedback::enums.evaluation_rating.'.$this->value.'.label');
    }
}
