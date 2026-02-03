<?php

declare(strict_types=1);

namespace He4rt\App\Filament\Widgets;

use Filament\Widgets\Widget;
use He4rt\Applications\Enums\ApplicationStatusEnum;
use He4rt\Applications\Models\Application;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Collection;

class UserApplicationsBreakdown extends Widget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?string $pollingInterval = null;

    protected string $view = 'panel-app::filament.widgets.user-applications-breakdown';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $userId = auth()->id();

        $statusCounts = Application::query()
            ->whereHas('candidate', fn (Builder $q) => $q->where('user_id', $userId))
            ->get()
            ->groupBy('status')
            ->map(fn (Collection $group) => $group->count());

        return [
            'breakdown' => [
                [
                    'label' => __('panel-app::filament.widgets.user_breakdown.in_review'),
                    'value' => ($statusCounts[ApplicationStatusEnum::New->value] ?? 0) + ($statusCounts[ApplicationStatusEnum::InReview->value] ?? 0),
                    'icon' => 'heroicon-o-clock',
                    'color' => 'text-yellow-500',
                ],
                [
                    'label' => __('panel-app::filament.widgets.user_breakdown.interview'),
                    'value' => $statusCounts[ApplicationStatusEnum::InProgress->value] ?? 0,
                    'icon' => 'heroicon-o-calendar',
                    'color' => 'text-purple-500',
                ],
                [
                    'label' => __('panel-app::filament.widgets.user_breakdown.offer'),
                    'value' => ($statusCounts[ApplicationStatusEnum::OfferExtended->value] ?? 0)
                        + ($statusCounts[ApplicationStatusEnum::OfferAccepted->value] ?? 0)
                        + ($statusCounts[ApplicationStatusEnum::OfferDeclined->value] ?? 0),
                    'icon' => 'heroicon-o-check-circle',
                    'color' => 'text-green-500',
                ],
                [
                    'label' => __('panel-app::filament.widgets.user_breakdown.rejected'),
                    'value' => ($statusCounts[ApplicationStatusEnum::Rejected->value] ?? 0) + ($statusCounts[ApplicationStatusEnum::Withdrawn->value] ?? 0),
                    'icon' => 'heroicon-o-x-circle',
                    'color' => 'text-red-500',
                ],
            ],
        ];
    }
}
