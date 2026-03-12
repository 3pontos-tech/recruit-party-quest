<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Widgets;

use Filament\Widgets\Widget;
use He4rt\Applications\Models\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class LatestApplicationsWidget extends Widget
{
    protected static ?int $sort = 3;

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'panel-organization::filament.widgets.latest-applications';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $teamId = filament()->getTenant()?->getKey();

        /** @var Collection<int, Application> $applications */
        $applications = Cache::remember(
            'widget.latest_applications.'.$teamId,
            now()->addMinutes(5),
            static fn () => Application::query()
                ->forCurrentTeam()
                ->with(['candidate.user', 'requisition.post', 'currentStage'])
                ->latest()
                ->limit(6)
                ->get()
        );

        return [
            'applications' => $applications,
        ];
    }
}
