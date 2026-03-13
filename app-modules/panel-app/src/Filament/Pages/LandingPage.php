<?php

declare(strict_types=1);

namespace He4rt\App\Filament\Pages;

use Filament\Pages\Dashboard;
use Filament\Support\Enums\Width;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

class LandingPage extends Dashboard
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
        return 'panel-app::filament.guest';
    }

    public function mount(): void
    {
        $this->registerMetaTags();
    }

    protected function registerMetaTags(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_START,
            fn (): string => Blade::render('components.metatags', [
                'url' => config('app.url'),
                'title' => 'Seja bem vindo à 3Pontos',
                'description' => 'Somos o ecossistema que une solução e conhecimento em um único lugar aceleramos sua empresa enquanto fortalecemos sua carreira.',
                'coverImage' => asset('images/seo.png'),
            ]),
        );
    }
}
