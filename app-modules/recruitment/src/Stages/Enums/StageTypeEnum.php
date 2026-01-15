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
    case Screening = 'screening';
    case Assessment = 'assessment';
    case Interview = 'interview';
    case Offer = 'offer';
    case Hired = 'hired';
    case Rejected = 'rejected';

    public function getColor(): array
    {
        return match ($this) {
            self::Screening => Color::Yellow,
            self::Assessment => Color::Blue,
            self::Interview => Color::Emerald,
            self::Offer => Color::Purple,
            self::Hired => Color::Green,
            self::Rejected => Color::Red,
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Screening => Heroicon::CursorArrowRipple,
            self::Assessment => Heroicon::ChatBubbleOvalLeftEllipsis,
            self::Interview => Heroicon::BuildingOffice2,
            self::Offer => Heroicon::Briefcase,
            self::Hired => Heroicon::CheckCircle,
            self::Rejected => Heroicon::XCircle,
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Screening => 'Triagem',
            self::Assessment => 'Avaliação',
            self::Interview => 'Entrevista',
            self::Offer => 'Proposta',
            self::Hired => 'Contratado',
            self::Rejected => 'Rejeitado',
        };
    }
}
