<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use AlizHarb\ActivityLog\ActivityLogPlugin;
use App\Enums\FilamentPanel;
use App\Filament\Shared\Pages\LoginPage;
use Filament\Enums\ThemeMode;
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
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;

final class AdminPanelProvider extends PanelProvider
{
    private FilamentPanel $panelId = FilamentPanel::Admin;

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->path($this->panelId->value)
            ->id($this->panelId->value)
            ->login(LoginPage::class)
            ->topbar()
            ->sidebarFullyCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::Purple,
                'success' => Color::Green,
                'warning' => Color::Yellow,
                'danger' => Color::Red,
                'info' => Color::Indigo,
                'gray' => Color::Gray,
            ])
            ->viteTheme(sprintf('resources/css/filament/%s/theme.css', $this->panelId->value))
            ->defaultThemeMode(ThemeMode::Dark)
            ->discoverClusters(in: base_path('app-modules/panel-admin/src/Filament/Clusters'), for: 'He4rt\\Admin\\Filament\\Clusters')
            ->discoverPages(in: base_path('app-modules/panel-admin/src/Filament/Pages'), for: 'He4rt\\Admin\\Filament\\Pages')
            ->discoverResources(in: base_path('app-modules/panel-admin/src/Filament/Resources'), for: 'He4rt\\Admin\\Filament\\Resources')
            ->discoverWidgets(in: base_path('app-modules/panel-admin/src/Filament/Widgets'), for: 'He4rt\\Admin\\Filament\\Widgets')
            ->topbar(false)
            ->pages([
                Dashboard::class,
            ])
            ->plugins([
                ActivityLogPlugin::make()
                    ->label('Log')
                    ->pluralLabel('Logs')
                    ->navigationGroup('System'),
                BreezyCore::make()
                    ->myProfile(
                        shouldRegisterUserMenu: true, // Sets the navigation group for the My Profile page (default = null)
                        hasAvatars: false,
                        // Customizes the 'account' link label in the panel User Menu (default = null)
                        navigationGroup: 'Settings',
                        // Sets the 'account' link in the panel User Menu (default = true)
                        userMenuLabel: 'My Profile', // Enables the avatar upload form component (default = false)
                    )
                    ->enableBrowserSessions(),
            ])
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
