<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Enums\FilamentPanel;
use DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin;
use DutchCodingCompany\FilamentSocialite\Provider;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use He4rt\App\Filament\Pages\AppDashboard;
use He4rt\App\Filament\Pages\AppLoginPage;
use He4rt\App\Livewire\MyProfile\CandidateEducation;
use He4rt\App\Livewire\MyProfile\CandidatePreferences;
use He4rt\App\Livewire\MyProfile\CandidateProfileInfo;
use He4rt\App\Livewire\MyProfile\CandidateSkills;
use He4rt\App\Livewire\MyProfile\CandidateWorkExperience;
use He4rt\App\RedirectIfOnboardingIncomplete;
use He4rt\Users\User;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
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
            ->login(AppLoginPage::class)
            ->registration()
            ->topNavigation()
            ->brandLogo(asset('images/3pontos/logo-compact.svg'))
            ->maxContentWidth(Width::ScreenTwoExtraLarge)
            ->path($this->panelEnum->getPath())
            ->colors([
                'primary' => Color::Gray,
                'success' => Color::Green,
                'warning' => Color::Yellow,
                'danger' => Color::Red,
                'info' => Color::Indigo,
                'gray' => Color::Gray,
            ])
            ->renderHook(PanelsRenderHook::SIDEBAR_NAV_END, fn () => Blade::render(<<<'BLADE'
               @guest
                    <div class="flex flex-col md:hidden mt-auto items-center space-y-4">
                        <x-he4rt::button rel="no-opener no-referrer" href="/login" icon="heroicon-s-arrow-top-right-on-square" variant="outline">
                            Acessar Plataforma
                        </x-he4rt::button>
                    </div>
               @endguest
            BLADE
            ))
            ->renderHook(PanelsRenderHook::TOPBAR_END, fn () => Blade::render(<<<'BLADE'
               @guest
                    <div class="hidden md:flex items-center space-x-4">
                        <x-he4rt::button rel="no-opener no-referrer" href="/login" icon="heroicon-s-arrow-top-right-on-square" variant="outline">
                            Acessar Plataforma
                        </x-he4rt::button>
                    </div>
               @endguest
            BLADE
            ))
            ->viteTheme('app-modules/he4rt/resources/css/themes/3pontos/theme.css')
            ->discoverClusters(in: base_path('app-modules/panel-app/src/Filament/Clusters'), for: 'He4rt\\App\\Filament\\Clusters')
            ->discoverPages(in: base_path('app-modules/panel-app/src/Filament/Pages'), for: 'He4rt\\App\\Filament\\Pages')
            ->discoverResources(in: base_path('app-modules/panel-app/src/Filament/Resources'), for: 'He4rt\\App\\Filament\\Resources')
            ->discoverWidgets(in: base_path('app-modules/panel-app/src/Filament/Widgets'), for: 'He4rt\\App\\Filament\\Widgets')
            ->pages([
                AppDashboard::class,
            ])
            ->plugins([
                BreezyCore::make()
                    ->myProfile(
                        shouldRegisterUserMenu: true,
                        hasAvatars: false,
                    )
                    ->myProfileComponents([
                        'candidate_profile_info' => CandidateProfileInfo::class,
                        'candidate_preferences' => CandidatePreferences::class,
                        'candidate_education' => CandidateEducation::class,
                        'candidate_work_experience' => CandidateWorkExperience::class,
                        'candidate_skills' => CandidateSkills::class,
                    ])
                    ->enableBrowserSessions(),
                FilamentSocialitePlugin::make()
                    ->providers([
                        Provider::make('google')
                            ->label('Google')
                            ->icon('fab-google')
                            ->color(Color::Red),
                        Provider::make('github')
                            ->label('GitHub')
                            ->icon('fab-github')
                            ->color(Color::Zinc),
                        Provider::make('linkedin-openid')
                            ->label('LinkedIn')
                            ->icon('fab-linkedin')
                            ->color(Color::Sky),
                        Provider::make('discord')
                            ->label('Discord')
                            ->icon('fab-discord')
                            ->color(Color::Indigo),
                    ])
                    ->registration(true)
                    ->userModelClass(User::class),
            ])
            ->discoverWidgets(in: app_path('Filament/App/Widgets'), for: 'He4rt\App\Filament\Widgets')
            ->globalSearch(false)
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
