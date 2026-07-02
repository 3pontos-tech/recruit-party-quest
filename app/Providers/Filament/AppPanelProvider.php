<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Enums\FilamentPanel;
use App\Providers\Filament\Hooks\AppPanelHooks;
use App\Socialite\CreateUserFromOauth;
use DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin;
use DutchCodingCompany\FilamentSocialite\Provider;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use He4rt\App\Filament\Pages\AppDashboard;
use He4rt\App\Filament\Pages\AppLoginPage;
use He4rt\App\Filament\Pages\CandidateMyProfilePage;
use He4rt\App\Filament\Pages\LandingPage;
use He4rt\App\Livewire\MyProfile\CandidateEducation;
use He4rt\App\Livewire\MyProfile\CandidateLinks;
use He4rt\App\Livewire\MyProfile\CandidatePreferences;
use He4rt\App\Livewire\MyProfile\CandidateProfileInfo;
use He4rt\App\Livewire\MyProfile\CandidateResumeUpload;
use He4rt\App\Livewire\MyProfile\CandidateSkills;
use He4rt\App\Livewire\MyProfile\CandidateWorkExperience;
use He4rt\App\RedirectIfOnboardingIncomplete;
use He4rt\PluginSeo\Metadata;
use He4rt\PluginSeo\SeoPlugin;
use He4rt\Term\Filament\Pages\TermPage;
use He4rt\Users\User;
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
            ->login(AppLoginPage::class)
            ->registration()
            ->topNavigation()
            ->brandLogo(fn () => view('partials.logo-compact'))
            ->favicon(asset('favicon.ico'))
            ->maxContentWidth(Width::ScreenTwoExtraLarge)
            ->databaseNotifications()
            ->path($this->panelEnum->getPath())
            ->colors([
                'primary' => Color::Gray,
                'success' => Color::Green,
                'warning' => Color::Yellow,
                'danger' => Color::Red,
                'info' => Color::Indigo,
                'gray' => Color::Gray,
            ])
            ->tap(fn (Panel $panel) => AppPanelHooks::register($panel))
            ->viteTheme('app-modules/he4rt/resources/css/themes/3pontos/theme.css')
            ->discoverClusters(in: base_path('app-modules/panel-app/src/Filament/Clusters'), for: 'He4rt\\App\\Filament\\Clusters')
            ->discoverPages(in: base_path('app-modules/panel-app/src/Filament/Pages'), for: 'He4rt\\App\\Filament\\Pages')
            ->discoverResources(in: base_path('app-modules/panel-app/src/Filament/Resources'), for: 'He4rt\\App\\Filament\\Resources')
            ->discoverWidgets(in: base_path('app-modules/panel-app/src/Filament/Widgets'), for: 'He4rt\\App\\Filament\\Widgets')
            ->pages([
                LandingPage::class,
                AppDashboard::class,
                TermPage::class,
            ])
            ->plugins([
                SeoPlugin::make()
                    ->defaults(
                        Metadata::make()
                            ->ogImage(asset('images/seo.png'))
                            ->description('Somos o ecossistema que une solução e conhecimento em um único lugar.')
                    ),
                BreezyCore::make()
                    ->myProfile()
                    ->customMyProfilePage(CandidateMyProfilePage::class)
                    ->myProfileComponents([
                        'candidate_resume_upload' => CandidateResumeUpload::class,
                        'candidate_profile_info' => CandidateProfileInfo::class,
                        'candidate_preferences' => CandidatePreferences::class,
                        'candidate_education' => CandidateEducation::class,
                        'candidate_work_experience' => CandidateWorkExperience::class,
                        'candidate_skills' => CandidateSkills::class,
                        'candidate_links' => CandidateLinks::class,
                    ])
                    ->enableBrowserSessions(),
                FilamentSocialitePlugin::make()
                    ->providers([
                        Provider::make('google')
                            ->label('Google')
                            ->visible(config('services.google.client_id') && config('services.google.client_secret'))
                            ->icon('fab-google'),
                        Provider::make('github')
                            ->label('GitHub')
                            ->visible(config('services.github.client_id') && config('services.github.client_secret'))
                            ->icon('fab-github'),
                        Provider::make('linkedin-openid')
                            ->label('LinkedIn')
                            ->visible(config('services.linkedin-openid.client_id') && config('services.linkedin-openid.client_secret'))
                            ->icon('fab-linkedin'),
                    ])
                    ->registration()
                    ->createUserUsing(fn (string $provider, \Laravel\Socialite\Contracts\User $oauthUser, FilamentSocialitePlugin $plugin) => resolve(CreateUserFromOauth::class)->handle($oauthUser))
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
