<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Stages\Enums;

use App\Enums\Concerns\StringifyEnum;
use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum StageTypeEnum: string implements HasColor, HasIcon, HasLabel
{
    use StringifyEnum;

    case New = 'new';

    case HiddenStage = 'hidden';

    case Screening = 'screening';
    case Assessment = 'assessment';
    case Interview = 'interview';
    case Offer = 'offer';
    case Hired = 'hired';

    public function getColor(): array
    {
        return match ($this) {
            self::New => Color::Gray,
            self::Screening => Color::Yellow,
            self::Assessment => Color::Blue,
            self::Interview => Color::Emerald,
            self::Offer => Color::Green,
            self::Hired => Color::Emerald,
            self::HiddenStage => Color::Red,
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::New, self::HiddenStage => Heroicon::ArchiveBox,
            self::Screening => Heroicon::CursorArrowRipple,
            self::Assessment => Heroicon::ChatBubbleOvalLeftEllipsis,
            self::Interview => Heroicon::BuildingOffice2,
            self::Offer => Heroicon::Briefcase,
            self::Hired => Heroicon::CheckCircle,
        };
    }

    public function getLabel(): string
    {
        return __('recruitment::stage_type.'.$this->value.'.label');
    }

    public function getTailwindColorClass(): string
    {
        return match ($this) {
            self::New => 'bg-gray-600',
            self::Screening => 'bg-yellow-500',
            self::Assessment => 'bg-blue-500',
            self::Interview, self::Hired => 'bg-emerald-500',
            self::Offer => 'bg-green-500',
            self::HiddenStage => 'bg-red-500',
        };
    }

    public function getBadgeClasses(): string
    {
        return match ($this) {
            self::New => 'bg-gray-600/10 text-gray-600',
            self::Screening => 'bg-yellow-500/10 text-yellow-500',
            self::Assessment => 'bg-blue-500/10 text-blue-500',
            self::Interview, self::Hired => 'bg-emerald-500/10 text-emerald-500',
            self::Offer => 'bg-green-500/10 text-green-500',
            self::HiddenStage => 'bg-red-500/10 text-red-500',
        };
    }
}
