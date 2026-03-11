<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use He4rt\Applications\Models\Application;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class ApplicationsPerDayWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $pollingInterval = null;

    public function getHeading(): string
    {
        return __('panel-organization::filament.widgets.applications_per_day.heading');
    }

    protected function getType(): string
    {
        return 'line';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $days = collect(range(29, 0))->map(fn (int $i) => Date::today()->subDays($i)->format('Y-m-d'));

        $counts = Application::query()
            ->forCurrentTeam()
            ->where('created_at', '>=', Date::today()->subDays(29))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->pluck('count', 'date');

        return [
            'datasets' => [
                [
                    'label' => __('panel-organization::filament.widgets.applications_per_day.dataset_label'),
                    'data' => $days->map(fn (string $date) => $counts->get($date, 0))->values()->all(),
                    'fill' => false,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $days->map(fn (string $date) => Date::parse($date)->format('M d'))->values()->all(),
        ];
    }
}
