<?php

declare(strict_types=1);

namespace He4rt\App\Filament\Resources\Applications\Pages;

use Filament\Resources\Pages\ListRecords;
use He4rt\App\Filament\Resources\Applications\ApplicationResource;

class ListApplications extends ListRecords
{
    protected static string $resource = ApplicationResource::class;
}
