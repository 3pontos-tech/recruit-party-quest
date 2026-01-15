<?php

namespace He4rt\Recruitment\Requisitions;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum RequisitionStatusEnum: string implements HasLabel, HasIcon, HasColor
{
    case Draft = 'draft';
    case PendingApproval = 'pending_approval';
    case Approved = 'approved';
    case Published = 'published';
    case OnHold = 'on_hold';
    case Closed = 'closed';
    case Cancelled = 'cancelled';

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'secondary',
            self::PendingApproval => 'warning',
            self::Approved => 'success',
            self::Published => 'primary',
            self::OnHold => 'warning',
            self::Closed => 'secondary',
            self::Cancelled => 'danger',
        };

    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Draft => Heroicon::Pencil,
            self::PendingApproval => Heroicon::Clock,
            self::Approved => Heroicon::Check,
            self::Published => Heroicon::Flag,
            self::OnHold => Heroicon::Pause,
            self::Closed => Heroicon::XMark,
            self::Cancelled => Heroicon::XCircle,
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'Rascunho',
            self::PendingApproval => 'Pendente de aprovação',
            self::Approved => 'Aprovado',
            self::Published => 'Publicado',
            self::OnHold => 'Em espera',
            self::Closed => 'Fechado',
            self::Cancelled => 'Cancelado',
        };
    }
}
