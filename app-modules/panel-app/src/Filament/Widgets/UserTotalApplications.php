<?php

declare(strict_types=1);

namespace He4rt\App\Filament\Widgets;

use Filament\Support\Colors\Color;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Models\Application;
use Illuminate\Contracts\Database\Query\Builder;

class UserTotalApplications extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $userId = auth()->id();

        $totalApplications = Application::query()
            ->whereHas('candidate', fn (Builder $q) => $q->where('user_id', $userId))
            ->count();

        $activeApplications = Application::query()
            ->whereHas('candidate', fn (Builder $q) => $q->where('user_id', $userId))
            ->whereIn('status', [
                ApplicationStatusEnum::New,
                ApplicationStatusEnum::InReview,
                ApplicationStatusEnum::InProgress,
                ApplicationStatusEnum::OfferExtended,
            ])
            ->count();

        $offersReceived = Application::query()
            ->whereHas('candidate', fn (Builder $q) => $q->where('user_id', $userId))
            ->whereIn('status', [
                ApplicationStatusEnum::OfferExtended,
                ApplicationStatusEnum::OfferAccepted,
                ApplicationStatusEnum::OfferDeclined,
            ])
            ->count();

        return [
            Stat::make(__('panel-app::filament.widgets.user_stats.total_applications'), (string) $totalApplications)
                ->description(__('panel-app::filament.widgets.user_stats.total_applications_description'))
                ->color(Color::Blue)
                ->descriptionIcon('heroicon-m-document-text'),
            Stat::make(__('panel-app::filament.widgets.user_stats.active_applications'), (string) $activeApplications)
                ->description(__('panel-app::filament.widgets.user_stats.active_applications_description'))
                ->color(Color::Purple)
                ->descriptionIcon('heroicon-m-clock'),
            Stat::make(__('panel-app::filament.widgets.user_stats.offers_received'), (string) $offersReceived)
                ->description(__('panel-app::filament.widgets.user_stats.offers_received_description'))
                ->color(Color::Green)
                ->descriptionIcon('heroicon-m-trophy'),
        ];
    }
}
