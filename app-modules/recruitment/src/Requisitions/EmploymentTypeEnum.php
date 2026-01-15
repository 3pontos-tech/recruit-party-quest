<?php

namespace He4rt\Recruitment\Requisitions;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum EmploymentTypeEnum: string implements HasLabel, HasIcon, HasColor
{
    case FullTimeEmployee = 'full_time_employee';
    case Contractor = 'contractor';
    case Intern = 'intern';
    case Temporary = 'temporary';
    case PartTime = 'part_time';

    public function getColor(): string
    {
        return match ($this) {
            self::FullTimeEmployee => 'primary',
            self::Contractor => 'secondary',
            self::Intern => 'success',
            self::Temporary => 'warning',
            self::PartTime => 'info',
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
        return match ($this) {
            self::FullTimeEmployee => 'Tempo integral',
            self::Contractor => 'Contratado',
            self::Intern => 'Estágio',
            self::Temporary => 'Temporário',
            self::PartTime => 'Meio período',
        };
    }
}
