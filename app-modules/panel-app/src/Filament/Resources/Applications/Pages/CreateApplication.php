<?php

declare(strict_types=1);

namespace He4rt\App\Filament\Resources\Applications\Pages;

use Filament\Resources\Pages\CreateRecord;
use He4rt\App\Filament\Resources\Applications\ApplicationResource;

class CreateApplication extends CreateRecord
{
    protected static string $resource = ApplicationResource::class;
}
