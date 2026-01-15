<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Requisitions\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum EmploymentTypeEnum: string implements HasColor, HasIcon, HasLabel
{
    case FullTimeEmployee = 'full_time_employee';
    case Contractor = 'contractor';
    case Intern = 'intern';
    case Temporary = 'temporary';
    case PartTime = 'part_time';

    public function getColor(): array
    {
        return match ($this) {
            self::FullTimeEmployee => Color::Emerald,
            self::Contractor => Color::Blue,
            self::Intern => Color::Lime,
            self::Temporary => Color::Amber,
            self::PartTime => Color::Sky,
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::FullTimeEmployee => Heroicon::Briefcase,
            self::Contractor => Heroicon::DocumentText,
            self::Intern => Heroicon::AcademicCap,
            self::Temporary => Heroicon::Calendar,
            self::PartTime => Heroicon::Clock,
        };
    }

    public function getLabel(): string
    {
        return __('recruitment::requisitions.employment_type.'.$this->value.'.label');
    }
}
