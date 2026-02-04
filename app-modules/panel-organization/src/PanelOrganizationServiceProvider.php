<?php

declare(strict_types=1);

namespace He4rt\Organization;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class PanelOrganizationServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'panel-organization');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'panel-organization');
        Blade::anonymousComponentPath(
            __DIR__.'/../resources/views/components',
            'panel-organization'
        );
    }
}
