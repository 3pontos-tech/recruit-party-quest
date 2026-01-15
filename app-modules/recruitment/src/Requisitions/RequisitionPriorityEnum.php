<?php

namespace He4rt\Recruitment\Requisitions;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum RequisitionPriorityEnum: string implements HasLabel, HasIcon, HasColor
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Urgent = 'urgent';

    public function getColor(): string
    {
        return match ($this) {
            self::Low => 'success',
            self::Medium => 'warning',
            self::High => 'danger',
            self::Urgent => 'danger',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Low => Heroicon::Check,
            self::Medium => Heroicon::Clock,
            self::High => Heroicon::Flag,
            self::Urgent => Heroicon::XCircle,
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Low => 'Baixa',
            self::Medium => 'Média',
            self::High => 'Alta',
            self::Urgent => 'Urgente',
        };
    }
}
