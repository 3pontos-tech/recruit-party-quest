<?php

declare(strict_types=1);

namespace He4rt\Teams;

use He4rt\Teams\Policies\TeamPolicy;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class TeamsServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'teams');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'teams');

        Gate::policy(Team::class, TeamPolicy::class);

        Relation::morphMap([
            'teams' => Team::class,
            'departments' => Department::class,
        ]);
    }
}
