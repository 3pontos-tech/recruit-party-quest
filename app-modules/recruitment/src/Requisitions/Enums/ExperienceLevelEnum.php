<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Requisitions\Enums;

use App\Enums\Concerns\StringifyEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum ExperienceLevelEnum: string implements HasColor, HasIcon, HasLabel
{
    use StringifyEnum;

    case Intern = 'intern';
    case EntryLevel = 'entry_level';
    case MidLevel = 'mid_level';
    case Senior = 'senior';
    case Lead = 'lead';
    case Principal = 'principal';

    public function getColor(): string
    {
        return match ($this) {
            self::Intern => 'secondary',
            self::EntryLevel => 'success',
            self::MidLevel => 'warning',
            self::Senior => 'danger',
            self::Lead => 'primary',
            self::Principal => 'info',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Intern => Heroicon::AcademicCap,
            self::EntryLevel => Heroicon::User,
            self::MidLevel => Heroicon::Briefcase,
            self::Senior => Heroicon::Star,
            self::Lead => Heroicon::UserGroup,
            self::Principal => Heroicon::ShieldCheck,
        };
    }

    public function getLabel(): string
    {
        return __('recruitment::requisitions.experience_level.'.$this->value.'.label');
    }
}
