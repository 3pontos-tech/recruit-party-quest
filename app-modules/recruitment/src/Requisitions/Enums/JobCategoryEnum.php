<?php

declare(strict_types=1);

namespace He4rt\Recruitment\Requisitions\Enums;

use App\Enums\Concerns\StringifyEnum;
use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum JobCategoryEnum: string implements HasColor, HasIcon, HasLabel
{
    use StringifyEnum;

    case Finance = 'finance';
    case Technology = 'technology';
    case Marketing = 'marketing';
    case HumanResources = 'human_resources';
    case Sales = 'sales';
    case Operations = 'operations';
    case Engineering = 'engineering';
    case Design = 'design';

    public function getColor(): array
    {
        return match ($this) {
            self::Finance => Color::Emerald,
            self::Technology => Color::Blue,
            self::Marketing => Color::Orange,
            self::HumanResources => Color::Purple,
            self::Sales => Color::Yellow,
            self::Operations => Color::Gray,
            self::Engineering => Color::Indigo,
            self::Design => Color::Pink,
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Finance => Heroicon::Banknotes,
            self::Technology => Heroicon::ComputerDesktop,
            self::Marketing => Heroicon::Megaphone,
            self::HumanResources => Heroicon::UserGroup,
            self::Sales => Heroicon::ShoppingCart,
            self::Operations => Heroicon::Cog6Tooth,
            self::Engineering => Heroicon::Wrench,
            self::Design => Heroicon::PaintBrush,
        };
    }

    public function getLabel(): string
    {
        return __('recruitment::requisitions.job_category.'.$this->value.'.label');
    }

    public function getDescription(): string
    {
        return __('recruitment::requisitions.job_category.'.$this->value.'.description');
    }

    /**
     * Get average salary in BRL.
     */
    public function getAverageSalary(): int
    {
        return match ($this) {
            self::Finance => 12000,
            self::Technology => 15000,
            self::Marketing => 8000,
            self::HumanResources => 7000,
            self::Sales => 9000,
            self::Operations => 6500,
            self::Engineering => 14000,
            self::Design => 10000,
        };
    }
}
