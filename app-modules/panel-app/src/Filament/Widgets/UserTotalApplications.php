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
        // TODO: o numero na descrição deve ser dinâmico.
        // TODO: Remover o numero do arquivo de tradução.
        return [
            Stat::make(__('panel-app::filament.widgets.user_total_applications.unique_views'), '192.1k')
                ->description(__('panel-app::filament.widgets.user_total_applications.unique_views_description'))
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color(Color::Green)
                ->descriptionIcon('heroicon-m-arrow-trending-up'),
            Stat::make(__('panel-app::filament.widgets.user_total_applications.bounce_rate'), '21%')
                ->description(__('panel-app::filament.widgets.user_total_applications.bounce_rate_description'))
                ->color(Color::Red)
                ->descriptionIcon('heroicon-m-arrow-trending-down'),
            Stat::make(__('panel-app::filament.widgets.user_total_applications.unique_views'), '192.1k')
                ->description(__('panel-app::filament.widgets.user_total_applications.unique_views_description'))
                ->color(Color::Green)
                ->descriptionIcon('heroicon-m-arrow-trending-up', IconPosition::Before),
        ];
    }
}
