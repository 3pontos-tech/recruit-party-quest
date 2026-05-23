<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Requisitions\Enums;

use App\Enums\Concerns\StringifyEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum WorkScheduleEnum: string implements HasColor, HasIcon, HasLabel
{
    use StringifyEnum;

    case FullTime = 'full_time';
    case PartTime = 'part_time';
    case Hourly = 'hourly';
    case Shift = 'shift';

    public function getColor(): string
    {
        return match ($this) {
            self::FullTime => 'success',
            self::PartTime => 'info',
            self::Hourly => 'warning',
            self::Shift => 'primary',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::FullTime => Heroicon::Clock,
            self::PartTime => Heroicon::Clock,
            self::Hourly => Heroicon::CurrencyDollar,
            self::Shift => Heroicon::ArrowPath,
        };
    }

    public function getLabel(): string
    {
        return __('recruitment::requisitions.work_schedule.'.$this->value.'.label');
    }
}
