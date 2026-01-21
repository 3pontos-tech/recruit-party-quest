<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Enums\FilamentPanel;
use App\Filament\Shared\Pages\LoginPage;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use He4rt\Organization\Filament\Pages\Tenancy\RegisterTenant;
use He4rt\Teams\Team;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class OrganizationPanelProvider extends PanelProvider
{
    private FilamentPanel $panelEnum = FilamentPanel::Organization;

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id($this->panelEnum->value)
            ->path($this->panelEnum->getPath())
            ->tenant(model: Team::class, slugAttribute: 'slug')
            ->login(LoginPage::class)
            ->topbar(false)
            ->colors([
                'primary' => Color::Gray,
                'success' => Color::Green,
                'warning' => Color::Yellow,
                'danger' => Color::Red,
                'info' => Color::Indigo,
                'gray' => Color::Gray,
            ])
            ->viteTheme(sprintf('resources/css/filament/%s/theme.css', $this->panelEnum->value))
            ->tenantRegistration(RegisterTenant::class)
            ->discoverClusters(in: base_path('app-modules/panel-organization/src/Filament/Clusters'), for: 'He4rt\\Organization\\Filament\\Clusters')
            ->discoverPages(in: base_path('app-modules/panel-organization/src/Filament/Pages'), for: 'He4rt\\Organization\\Filament\\Pages')
            ->discoverResources(in: base_path('app-modules/panel-organization/src/Filament/Resources'), for: 'He4rt\\Organization\\Filament\\Resources')
            ->discoverWidgets(in: base_path('app-modules/panel-organization/src/Filament/Widgets'), for: 'He4rt\\Organization\\Filament\\Widgets')
            ->discoverResources(in: app_path('Filament/Organization/Resources'), for: 'App\Filament\Organization\Resources')
            ->discoverPages(in: app_path('Filament/Organization/Pages'), for: 'App\Filament\Organization\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Organization/Widgets'), for: 'App\Filament\Organization\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
