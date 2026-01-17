<?php

declare(strict_types=1);

namespace He4rt\App\Filament\Widgets;

use Filament\Widgets\Widget;

class UserApplicationsBreakdown extends Widget
{
    protected int|string|array $columnSpan = 'full';

    protected string $view = 'panel-app::filament.widgets.user-applications-breakdown';
}
