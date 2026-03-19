<?php

declare(strict_types=1);

namespace He4rt\App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use He4rt\App\Filament\Widgets\UserTotalApplications;

class AppDashboard extends Page
{
    protected static ?string $slug = 'dashboard';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserCircle;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected static ?string $navigationLabel = 'Dashboard';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check();
    }

    public function getHeading(): string
    {
        return '';
    }

    public function getSubheading(): ?string
    {
        return null;
    }

    public function getView(): string
    {
        return 'panel-app::filament.app';
    }

    public function mount(): void
    {
        if (auth()->guest()) {
            redirect(route('filament.app.pages.landing-page'))->send();
        }
    }

    protected function getHeaderWidgets(): array
    {
        return [
            UserTotalApplications::make(),
            // UserApplicationsBreakdown::make(),
        ];
    }
}
