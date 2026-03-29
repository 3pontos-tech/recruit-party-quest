<?php

declare(strict_types=1);

namespace He4rt\PluginSeo;

use Illuminate\Support\ServiceProvider;

class SeoPluginServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/filament-seo.php', 'filament-seo');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'plugin-seo');

        $this->publishes([
            __DIR__.'/../config/filament-seo.php' => config_path('filament-seo.php'),
        ], 'filament-seo-config');
    }
}
