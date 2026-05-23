<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Requisitions\Enums;

use App\Enums\Concerns\StringifyEnum;
use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum EmploymentTypeEnum: string implements HasColor, HasIcon, HasLabel
{
    use StringifyEnum;

    case Clt = 'clt';
    case Contractor = 'contractor';
    case Temporary = 'temporary';
    case Freelancer = 'freelancer';
    case Intern = 'intern';

    public function getColor(): array
    {
        return match ($this) {
            self::Clt => Color::Teal,
            self::Contractor => Color::Blue,
            self::Temporary => Color::Amber,
            self::Freelancer => Color::Violet,
            self::Intern => Color::Lime,
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Clt => Heroicon::ShieldCheck,
            self::Contractor => Heroicon::DocumentText,
            self::Temporary => Heroicon::Calendar,
            self::Freelancer => Heroicon::Sparkles,
            self::Intern => Heroicon::AcademicCap,
        };
    }

    public function getLabel(): string
    {
        return __('recruitment::requisitions.employment_type.'.$this->value.'.label');
    }
}
