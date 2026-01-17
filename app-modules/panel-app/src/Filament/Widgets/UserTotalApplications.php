<?php

declare(strict_types=1);

namespace He4rt\App\Filament\Widgets;

use Filament\Support\Colors\Color;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserTotalApplications extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Unique views', '192.1k')
                ->description('32k increase')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color(Color::Green)
                ->descriptionIcon('heroicon-m-arrow-trending-up'),
            Stat::make('Bounce rate', '21%')
                ->description('7% decrease')
                ->color(Color::Red)
                ->descriptionIcon('heroicon-m-arrow-trending-down'),
            Stat::make('Unique views', '192.1k')
                ->description('32k increase')
                ->color(Color::Green)
                ->descriptionIcon('heroicon-m-arrow-trending-up', IconPosition::Before),
        ];
    }
}
