<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Requisitions\Enums;

use App\Enums\Concerns\StringifyEnum;
use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum ExperienceLevelEnum: string implements HasColor, HasIcon, HasLabel
{
    use StringifyEnum;

    case Intern = 'intern';
    case Trainee = 'trainee';
    case Junior = 'junior';
    case MidLevel = 'mid_level';
    case Senior = 'senior';
    case Specialist = 'specialist';
    case Coordinator = 'coordinator';
    case Manager = 'manager';
    case Head = 'head';
    case CLevel = 'c_level';
    case TalentPool = 'talent_pool';
    case Assistant = 'assistant';

    public function getColor(): array
    {
        return match ($this) {
            self::Intern => Color::Blue,
            self::Trainee => Color::Cyan,
            self::Junior => Color::Green,
            self::MidLevel => Color::Yellow,
            self::Senior => Color::Red,
            self::Specialist => Color::Teal,
            self::Coordinator, self::Manager => Color::Indigo,
            self::Head, self::CLevel => Color::Gray,
            self::TalentPool => Color::Purple,
            self::Assistant => Color::Pink,
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Intern => Heroicon::AcademicCap,
            self::Trainee => Heroicon::BookOpen,
            self::Junior => Heroicon::User,
            self::MidLevel => Heroicon::Briefcase,
            self::Senior => Heroicon::Star,
            self::Specialist => Heroicon::ShieldCheck,
            self::Coordinator => Heroicon::UserGroup,
            self::Manager => Heroicon::BuildingOffice,
            self::Head => Heroicon::CommandLine,
            self::CLevel => Heroicon::Trophy,
            self::TalentPool => Heroicon::Users,
            self::Assistant => Heroicon::UserCircle,
        };
    }

    public function getLabel(): string
    {
        return __('recruitment::requisitions.experience_level.'.$this->value.'.label');
    }
}
