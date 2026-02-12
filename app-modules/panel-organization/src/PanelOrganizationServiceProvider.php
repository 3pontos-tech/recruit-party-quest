<?php

declare(strict_types=1);

namespace He4rt\Organization;

use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use He4rt\Organization\Livewire\JobGenerationOverlay;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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

        // Register Livewire component
        Livewire::component('job-generation-overlay', JobGenerationOverlay::class);

        // Add overlay to all panel pages
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn (): string => Blade::render("@livewire('job-generation-overlay')")
        );
    }
}
