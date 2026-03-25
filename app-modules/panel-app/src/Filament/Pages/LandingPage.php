<?php

declare(strict_types=1);

namespace He4rt\App\Filament\Pages;

use Filament\Pages\Dashboard;
use Filament\Support\Enums\Width;
use He4rt\PluginSeo\Concerns\InteractsWithMetadata;
use He4rt\PluginSeo\Contracts\HasMetadata;
use He4rt\PluginSeo\Metadata;

class LandingPage extends Dashboard implements HasMetadata
{
    use InteractsWithMetadata;

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

    public function getMetadata(): Metadata
    {
        return Metadata::make()
            ->title('Seja bem vindo à 3Pontos')
            ->description('Somos o ecossistema que une solução e conhecimento em um único lugar aceleramos sua empresa enquanto fortalecemos sua carreira.')
            ->url(config('app.url'))
            ->ogImage(asset('images/seo.png'));
    }
}
