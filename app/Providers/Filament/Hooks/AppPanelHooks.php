<?php

declare(strict_types=1);

namespace App\Providers\Filament\Hooks;

use Filament\Panel;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Blade;

class AppPanelHooks
{
    public static function register(Panel $panel): void
    {
        self::registerSidebarHooks($panel);
        self::registerTopbarHooks($panel);
        self::registerFooterHooks($panel);
    }

    private static function registerSidebarHooks(Panel $panel): void
    {
        $panel->renderHook(
            PanelsRenderHook::SIDEBAR_NAV_END,
            fn () => Blade::render(<<<'BLADE'
               @guest
                    <div class="flex flex-col md:hidden mt-auto items-center space-y-4">
                        <x-he4rt::button rel="no-opener no-referrer" href="/login" icon="heroicon-s-arrow-top-right-on-square" variant="outline">
                            {{ __('panel-app::filament.navigation.access_platform') }}
                        </x-he4rt::button>
                    </div>
               @endguest
            BLADE)
        );
    }

    private static function registerTopbarHooks(Panel $panel): void
    {
        // Guest: Login button
        $panel->renderHook(
            PanelsRenderHook::TOPBAR_END,
            fn () => Blade::render(<<<'BLADE'
               @guest
                    <div class="hidden md:flex items-center space-x-4">
                        <x-he4rt::button rel="no-opener no-referrer" href="/login" icon="heroicon-s-arrow-top-right-on-square" variant="outline">
                            {{ __('panel-app::filament.navigation.access_platform') }}
                        </x-he4rt::button>
                    </div>
               @endguest
            BLADE)
        );

        // Auth: Saved jobs widget (before user menu)
        $panel->renderHook(
            PanelsRenderHook::USER_MENU_BEFORE,
            fn () => Blade::render(<<<'BLADE'
               @auth
                    <x-panel-app::jobs.saved-jobs-widget />
               @endauth
            BLADE)
        );
    }

    private static function registerFooterHooks(Panel $panel): void
    {
        $panel->renderHook(
            PanelsRenderHook::FOOTER,
            function (): null|Factory|View {
                if (request()->routeIs('filament.app.pages.onboarding')) {
                    return null;
                }

                if (request()->routeIs('filament.app.auth.login')) {
                    return null;
                }

                return view('panel-app::partials.app-footer');
            }
        );
    }
}
