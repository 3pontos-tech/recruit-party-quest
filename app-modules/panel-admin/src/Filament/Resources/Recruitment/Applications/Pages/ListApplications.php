<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Recruitment\Applications\Pages;

use Filament\Resources\Pages\ListRecords;
use He4rt\Admin\Filament\Resources\Recruitment\Applications\ApplicationResource;

class ListApplications extends ListRecords
{
    protected static string $resource = ApplicationResource::class;
}
