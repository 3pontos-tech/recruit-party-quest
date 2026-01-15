<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Permissions\Pages;

use Filament\Resources\Pages\CreateRecord;
use He4rt\Admin\Filament\Resources\Permissions\RoleResource;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
