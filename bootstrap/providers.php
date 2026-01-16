<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AppPanelProvider;
use App\Providers\FilamentServiceProvider;

return [
    AppServiceProvider::class,
    FilamentServiceProvider::class,
    AppPanelProvider::class,
    //    App\Providers\Filament\OrganizationPanelProvider::class,
    //    App\Providers\Filament\AdminPanelProvider::class,
];
