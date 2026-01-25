<?php

declare(strict_types=1);

namespace He4rt\Applications\Enums;

use App\Enums\Concerns\StringifyEnum;
use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum CandidateSourceEnum: string implements HasColor, HasIcon, HasLabel
{
    use StringifyEnum;

    case LinkedIn = 'linkedin';
    case Indeed = 'indeed';
    case Glassdoor = 'glassdoor';
    case Referral = 'referral';
    case CareerPage = 'career_page';
    case Other = 'other';

    public function getLabel(): string
    {
        return __('applications::enums.candidate_source.'.$this->value.'.label');
    }

    public function getColor(): array
    {
        return match ($this) {
            self::LinkedIn => Color::Blue,
            self::Indeed => Color::Emerald,
            self::Glassdoor => Color::Fuchsia,
            self::Referral => Color::Cyan,
            self::CareerPage => Color::Purple,
            self::Other => Color::Teal,
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::LinkedIn => Heroicon::Briefcase,
            self::Indeed => Heroicon::MagnifyingGlass,
            self::Glassdoor => Heroicon::BuildingOffice,
            self::Referral => Heroicon::UserGroup,
            self::CareerPage => Heroicon::GlobeAlt,
            self::Other => Heroicon::User,
        };
    }
}
