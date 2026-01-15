<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Requisitions;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum WorkArrangementEnum: string implements HasColor, HasIcon, HasLabel
{
    case Remote = 'remote';
    case Hybrid = 'hybrid';
    case OnSite = 'on_site';

    public function getColor(): string
    {
        return match ($this) {
            self::Remote => 'info',
            self::Hybrid => 'warning',
            self::OnSite => 'primary',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Remote => Heroicon::ComputerDesktop,
            self::Hybrid => Heroicon::GlobeAlt,
            self::OnSite => Heroicon::MapPin,
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Remote => 'Remoto',
            self::Hybrid => 'Híbrido',
            self::OnSite => 'Presencial',
        };
    }
}
