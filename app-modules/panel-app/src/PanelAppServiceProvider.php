<?php

declare(strict_types=1);

namespace He4rt\App;

use He4rt\App\Livewire\ResumeFileUploadProgress;
use He4rt\App\Livewire\UserLatestApplications;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class PanelAppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Livewire::component('user-latest-applications', UserLatestApplications::class);
        Livewire::component('resume-file-upload-progress', ResumeFileUploadProgress::class);
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'panel-app');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'panel-app');
    }
}
