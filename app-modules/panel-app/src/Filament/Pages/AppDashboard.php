<?php

declare(strict_types=1);

namespace He4rt\App\Filament\Pages;

use Filament\Pages\Dashboard;
use Filament\Widgets\AccountWidget;

class AppDashboard extends Dashboard
{
    public function getView(): string
    {
        return auth()->check()
            ? 'panel-app::filament.app.dashboard.app'
            : 'panel-app::filament.app.dashboard.guest';
    }

    protected function getHeaderWidgets(): array
    {
        if (auth()->check()) {
            return [
                AccountWidget::make(),
            ];
        }

        return [];
    }
}
