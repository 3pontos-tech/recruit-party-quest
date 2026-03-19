<?php

declare(strict_types=1);

namespace He4rt\Users;

use App\Enums\FilamentPanel;
use DutchCodingCompany\FilamentSocialite\Events\Login;
use DutchCodingCompany\FilamentSocialite\Events\Registered;
use Filament\Panel;
use He4rt\Admin\Filament\Resources\Users\UserResource;
use He4rt\Users\Listeners\SaveGitHubTokenListener;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class UsersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Panel::configureUsing(
            fn (Panel $panel) => match ($panel->currentPanel()) {
                FilamentPanel::Admin => $panel->resources([UserResource::class]),
                default => null
            }
        );
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'users');

        Relation::morphMap([
            'users' => User::class,
        ]);

        Event::listen([Login::class, Registered::class], SaveGitHubTokenListener::class);
    }
}
