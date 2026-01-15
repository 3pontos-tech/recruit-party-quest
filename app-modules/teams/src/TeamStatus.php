<?php

declare(strict_types=1);

namespace He4rt\Teams;

use BackedEnum;
use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum TeamStatus: string implements HasColor, HasIcon, HasLabel
{
    case Active = 'active';
    case Suspended = 'suspended';

    case Archived = 'archived';

    public function getLabel(): string
    {
        return __('teams::team_status.'.$this->value.'.label');
    }

    public function getColor(): array
    {
        return match ($this) {
            self::Active => Color::Green,
            self::Suspended => Color::Yellow,
            self::Archived => Color::Red,
        };
    }

    public function getIcon(): BackedEnum
    {
        return match ($this) {
            self::Active => Heroicon::CheckCircle,
            self::Suspended => Heroicon::ExclamationCircle,
            self::Archived => Heroicon::XCircle,
        };
    }
}
