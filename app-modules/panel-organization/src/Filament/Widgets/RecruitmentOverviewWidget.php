<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Widgets;

use Filament\Support\Colors\Color;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use He4rt\Applications\Models\Application;
use He4rt\Recruitment\Requisitions\Enums\RequisitionStatusEnum;
use He4rt\Recruitment\Requisitions\Models\JobRequisition;
use Illuminate\Support\Facades\DB;

class RecruitmentOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $openRequisitions = JobRequisition::query()
            ->forCurrentTeam()
            ->whereIn('status', [
                RequisitionStatusEnum::Approved,
                RequisitionStatusEnum::Published,
            ])
            ->count();

        $totalApplications = Application::query()
            ->forCurrentTeam()
            ->count();

        $offersExtended = Application::query()
            ->forCurrentTeam()
            ->whereNotNull('offer_extended_at')
            ->count();

        $positionsAvailable = JobRequisition::query()
            ->forCurrentTeam()
            ->whereIn('status', [
                RequisitionStatusEnum::Approved,
                RequisitionStatusEnum::Published,
            ])
            ->sum(DB::raw('CAST(positions_available AS INTEGER)'));

        return [
            Stat::make(__('panel-organization::filament.widgets.recruitment_overview.open_requisitions'), (string) $openRequisitions)
                ->description(__('panel-organization::filament.widgets.recruitment_overview.open_requisitions_description'))
                ->color(Color::Blue)
                ->descriptionIcon('heroicon-m-briefcase'),
            Stat::make(__('panel-organization::filament.widgets.recruitment_overview.total_applications'), (string) $totalApplications)
                ->description(__('panel-organization::filament.widgets.recruitment_overview.total_applications_description'))
                ->color(Color::Purple)
                ->descriptionIcon('heroicon-m-document-text'),
            Stat::make(__('panel-organization::filament.widgets.recruitment_overview.offers_extended'), (string) $offersExtended)
                ->description(__('panel-organization::filament.widgets.recruitment_overview.offers_extended_description'))
                ->color(Color::Green)
                ->descriptionIcon('heroicon-m-trophy'),
            Stat::make(__('panel-organization::filament.widgets.recruitment_overview.positions_available'), (string) $positionsAvailable)
                ->description(__('panel-organization::filament.widgets.recruitment_overview.positions_available_description'))
                ->color(Color::Amber)
                ->descriptionIcon('heroicon-m-user-group'),
        ];
    }
}
