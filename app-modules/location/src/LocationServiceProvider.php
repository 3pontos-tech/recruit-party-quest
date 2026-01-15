<?php

declare(strict_types=1);

namespace He4rt\Location;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class LocationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Relation::morphMap([
            'address' => Address::class,
        ]);
    }

    public function boot(): void {}
}
