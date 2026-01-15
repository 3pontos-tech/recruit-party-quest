<?php

declare(strict_types=1);

namespace He4rt\Location;

use He4rt\Location\Models\Address;
use He4rt\Location\Models\City;
use He4rt\Location\Models\State;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class LocationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Relation::morphMap([
            'address' => Address::class,
            'city' => City::class,
            'state' => State::class,
        ]);
    }

    public function boot(): void {}
}
