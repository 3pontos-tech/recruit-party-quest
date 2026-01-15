<?php

declare(strict_types=1);

namespace He4rt\Users;

use App\Enums\FilamentPanel;
use Filament\Panel;
use He4rt\Admin\Filament\Resources\Users\UserResource;
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
    }
}
