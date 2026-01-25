<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Stages\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum StageTypeEnum: string implements HasColor, HasIcon, HasLabel
{
    case New = 'new';

    case HiddenStage = 'hidden';

    case Screening = 'screening';
    case Assessment = 'assessment';
    case Interview = 'interview';
    case Offer = 'offer';
    case Hired = 'hired';
    case Rejected = 'rejected';
    case Declined = 'declined';

    public static function getColorClassFromString(string $stageType): string
    {
        $enum = self::tryFrom($stageType);

        return $enum?->getTailwindColorClass() ?? 'bg-slate-500';
    }

    public function getColor(): array
    {
        return match ($this) {
            self::New => Color::Gray,
            self::HiddenStage, self::Rejected, self::Declined => Color::Red,
            self::Screening => Color::Yellow,
            self::Assessment => Color::Blue,
            self::Interview => Color::Emerald,
            self::Offer => Color::Purple,
            self::Hired => Color::Green,
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
            self::Rejected, self::Declined => Heroicon::XCircle,
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
            self::Interview => 'bg-emerald-500',
            self::Offer => 'bg-purple-500',
            self::Hired => 'bg-green-500',
            self::Rejected, self::Declined => 'bg-red-500',
            self::HiddenStage => 'bg-slate-500',
        };
    }
}
