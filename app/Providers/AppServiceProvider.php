<?php

declare(strict_types=1);

namespace App\Providers;

use App\Filament\Shared\MyProfile\PersonalInfo;
use App\Providers\Tools\DebugbarServiceProvider;
use App\Providers\Tools\TelescopeServiceProvider;
use Carbon\CarbonImmutable;
use He4rt\Users\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use SocialiteProviders\Discord\Provider;
use SocialiteProviders\Manager\SocialiteWasCalled;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(Authenticatable::class, User::class);

        $this->registerDebugbar();
        $this->registerTelescope();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureCommands();
        $this->configureDatabase();
        $this->configureDates();
        $this->configureVite();
        $this->configureUrl();
        $this->configureHttp();
        $this->configureSocialite();
        $this->configureLivewireComponents();
    }

    /**
     * Aponta o alias `personal_info` do Breezy para a nossa subclasse.
     *
     * O Breezy registra `Livewire::component('personal_info', ...)` com a classe dele;
     * sem sobrescrever o alias, o primeiro render usa a nossa classe mas o round-trip
     * do Livewire ressuscita o componente como o do pacote, perdendo o override.
     */
    private function configureLivewireComponents(): void
    {
        $this->app->booted(static function (): void {
            Livewire::component('personal_info', PersonalInfo::class);
        });
    }

    /**
     * Configure the application's commands
     */
    private function configureCommands(): void
    {
        DB::prohibitDestructiveCommands($this->app->isProduction());
    }

    /**
     * Configure the application's models
     */
    private function configureDatabase(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());
        Model::automaticallyEagerLoadRelationships();
        Model::unguard();
        Builder::morphUsingUuids();

        Relation::morphMap(['user' => User::class]);
    }

    /**
     * Configure the application's Vite
     */
    private function configureVite(): void
    {
        if ($this->app->isProduction()) {
            Vite::useAggressivePrefetching();
        }
    }

    /**
     * Configure the dates.
     */
    private function configureDates(): void
    {
        Date::use(CarbonImmutable::class);
    }

    /**
     * Configure the application's URL
     */
    private function configureUrl(): void
    {
        URL::forceHttps($this->app->isProduction());
    }

    /**
     * Configure Http module
     */
    private function configureHttp(): void
    {
        if ($this->app->runningUnitTests()) {
            Http::preventStrayRequests();
        }
    }

    /**
     * Configure Socialite providers
     */
    private function configureSocialite(): void
    {
        Event::listen(function (SocialiteWasCalled $event): void {
            $event->extendSocialite('discord', Provider::class);
        });
    }

    private function registerDebugbar(): void
    {
        if ($this->app->isLocal() && class_exists(\Fruitcake\LaravelDebugbar\ServiceProvider::class)) {
            $this->app->register(DebugbarServiceProvider::class);
        }
    }

    private function registerTelescope(): void
    {
        if ($this->app->isLocal() && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(TelescopeServiceProvider::class);
        }
    }
}
