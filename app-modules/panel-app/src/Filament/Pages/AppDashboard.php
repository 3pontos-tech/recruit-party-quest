<?php

declare(strict_types=1);

namespace He4rt\App\Filament\Pages;

use Filament\Pages\Dashboard;
use Filament\Support\Enums\Width;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use He4rt\App\Filament\Widgets\UserApplicationsBreakdown;
use He4rt\App\Filament\Widgets\UserTotalApplications;
use Illuminate\Support\Facades\Blade;

class AppDashboard extends Dashboard
{
    protected static bool $shouldRegisterNavigation = false;

    protected Width|string|null $maxContentWidth = Width::Full;

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
        return auth()->check()
            ? 'panel-app::filament.app'
            : 'panel-app::filament.guest';
    }

    public function mount(): void
    {
        $this->registerMetaTags();
    }

    protected function getHeaderWidgets(): array
    {
        if (auth()->check()) {
            return [
                UserTotalApplications::make(),
                UserApplicationsBreakdown::make(),
            ];
        }

        return [];
    }

    protected function registerMetaTags(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_START,
            fn (): string => auth()->guest() ? Blade::render('components.metatags', [
                'url' => config('app.url'),
                'title' => 'Seja bem vindo à 3Pontos',
                'description' => 'Somos o ecossistema que une solução e conhecimento em um único lugar aceleramos sua empresa enquanto fortalecemos sua carreira.',
                'coverImage' => asset('images/seo.png'),
            ]) : '',
        );
    }
}
