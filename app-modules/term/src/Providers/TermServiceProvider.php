<?php

declare(strict_types=1);

namespace He4rt\Term\Providers;

use Illuminate\Support\ServiceProvider;

class TermServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../../lang', 'term');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'term');
    }
}
