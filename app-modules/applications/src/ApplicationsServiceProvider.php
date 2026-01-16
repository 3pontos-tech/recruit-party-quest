<?php

declare(strict_types=1);

namespace He4rt\Applications;

use He4rt\Applications\Models\Application;
use He4rt\Applications\Models\ApplicationStageHistory;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class ApplicationsServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'applications');

        Relation::morphMap([
            'applications' => Application::class,
            'application_stage_histories' => ApplicationStageHistory::class,
        ]);
    }
}
