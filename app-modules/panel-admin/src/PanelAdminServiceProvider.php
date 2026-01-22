<?php

declare(strict_types=1);

namespace He4rt\Admin;

use App\Enums\FilamentPanel;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;

class PanelAdminServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Panel::configureUsing(
            fn (Panel $panel) => match ($panel->currentPanel()) {
                FilamentPanel::Admin => $panel
                    ->discoverResources(in: modules_path('panel-admin/src/Filament/Resources'), for: 'He4rt\\Admin\\Filament\\Resources')
                    ->discoverPages(in: modules_path('panel-admin/src/Filament/Pages'), for: 'He4rt\\Admin\\Filament\\Pages')
                    ->discoverWidgets(in: modules_path('panel-admin/src/Filament/Widgets'), for: 'He4rt\\Admin\\Filament\\Widgets')
                    ->discoverClusters(in: modules_path('panel-admin/src/Filament/Clusters'), for: 'He4rt\\Admin\\Filament\\Clusters'),
                default => null,
            }
        );
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'panel-admin');
    }
}
