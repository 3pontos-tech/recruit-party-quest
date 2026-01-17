<?php

declare(strict_types=1);

namespace He4rt\App;

use Illuminate\Support\ServiceProvider;

class PanelAppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'panel-app');
    }
}
