<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\AppPanelProvider;
use App\Providers\Filament\OrganizationPanelProvider;
use App\Providers\FilamentServiceProvider;

return [
    AppServiceProvider::class,
    FilamentServiceProvider::class,
    AppPanelProvider::class,
    OrganizationPanelProvider::class,
    AdminPanelProvider::class,
];
