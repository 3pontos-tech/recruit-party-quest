<?php

declare(strict_types=1);

namespace He4rt\RepoAnalysis\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum AnalysisStatus: string implements HasColor, HasIcon, HasLabel
{
    case Pending = 'pending';
    case Analyzing = 'analyzing';
    case Completed = 'completed';
    case Failed = 'failed';

    public function getLabel(): string
    {
        return __('repo-analysis::labels.status.'.$this->value);
    }

    public function getColor(): array
    {
        return match ($this) {
            self::Pending => Color::Gray,
            self::Analyzing => Color::Blue,
            self::Completed => Color::Green,
            self::Failed => Color::Red,
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Pending => Heroicon::Clock,
            self::Analyzing => Heroicon::ArrowPath,
            self::Completed => Heroicon::CheckCircle,
            self::Failed => Heroicon::XCircle,
        };
    }
}
