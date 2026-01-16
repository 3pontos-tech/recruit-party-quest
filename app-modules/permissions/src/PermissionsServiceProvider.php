<?php

declare(strict_types=1);

namespace He4rt\Permissions;

use He4rt\Permissions\Commands\SyncPermissions\SyncPermissionsCommand;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class PermissionsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/permissions.php', 'permission');
        $this->commands(SyncPermissionsCommand::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'permissions');

        Relation::morphMap([
            'roles' => Role::class,
            'permissions' => Permission::class,
        ]);

        Gate::policy(Role::class, RolePolicy::class);
    }
}
