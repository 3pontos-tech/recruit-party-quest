<?php

declare(strict_types=1);

namespace He4rt\Organization;

use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use He4rt\Organization\Livewire\JobGenerationOverlay;
use He4rt\Organization\Livewire\PipelineStageDetail;
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

        Livewire::component('job-generation-overlay', JobGenerationOverlay::class);
        Livewire::component('pipeline-stage-detail', PipelineStageDetail::class);

        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            function (): string {
                if (! auth()->check()) {
                    return '';
                }

                return Blade::render(
                    "@livewire('job-generation-overlay', ['userId' => \$userId])",
                    ['userId' => auth()->id()]
                );
            }
        );
    }
}
