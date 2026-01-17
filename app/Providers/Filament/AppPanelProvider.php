<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Enums\FilamentPanel;
use App\Filament\Shared\Pages\LoginPage;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use He4rt\App\Filament\Pages\AppDashboard;
use He4rt\App\RedirectIfOnboardingIncomplete;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;

class AppPanelProvider extends PanelProvider
{
    private FilamentPanel $panelEnum = FilamentPanel::App;

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id($this->panelEnum->value)
            ->default()
            ->login(LoginPage::class)
            ->registration()
            ->topNavigation()
            ->maxContentWidth(Width::ScreenTwoExtraLarge)
            ->path($this->panelEnum->getPath())
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverClusters(in: base_path('app-modules/panel-app/src/Filament/Clusters'), for: 'He4rt\\App\\Filament\\Clusters')
            ->discoverPages(in: base_path('app-modules/panel-app/src/Filament/Pages'), for: 'He4rt\\App\\Filament\\Pages')
            ->discoverResources(in: base_path('app-modules/panel-app/src/Filament/Resources'), for: 'He4rt\\App\\Filament\\Resources')
            ->discoverWidgets(in: base_path('app-modules/panel-app/src/Filament/Widgets'), for: 'He4rt\\App\\Filament\\Widgets')
            ->pages([
                auth()->check() ? Dashboard::class : AppDashboard::class,
            ])
            ->plugins([
                BreezyCore::make()
                    ->myProfile(
                        shouldRegisterUserMenu: false, // Disable - we have custom profile page
                        hasAvatars: false,
                    )
                    ->enableBrowserSessions(),
            ])
            ->discoverWidgets(in: app_path('Filament/App/Widgets'), for: 'He4rt\App\Filament\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                RedirectIfOnboardingIncomplete::class,
            ]);
    }
}
